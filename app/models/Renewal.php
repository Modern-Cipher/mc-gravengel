<?php
// app/models/Renewal.php
class Renewal {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * FINAL FIX: Kinukuha ang LAHAT ng active burials.
     * Ang query ay isinulat sa pinakaligtas na paraan para isa-isang pangalanan
     * ang mga column at maiwasan ang "undefined" error.
     */
    public function getBurialsForRenewal() {
        $this->db->query("
            SELECT
                b.id,
                b.burial_id,
                b.plot_id,
                b.deceased_first_name,
                b.deceased_middle_name,
                b.deceased_last_name,
                b.deceased_suffix,
                b.interment_full_name,
                b.interment_email,
                b.expiry_date,
                p.plot_number,
                mb.title AS block_title
            FROM
                burials b
            LEFT JOIN
                plots p ON b.plot_id = p.id
            LEFT JOIN
                map_blocks mb ON p.map_block_id = mb.id
            WHERE
                b.is_active = 1
            ORDER BY
                b.expiry_date ASC
        ");
        return $this->db->resultSet();
    }

    /**
     * Kinukuha ang kumpletong detalye ng isang burial record para sa email.
     */
    public function getDetailedBurialForRenewal($burialId) {
        $this->db->query("
            SELECT b.*, p.plot_number, mb.title AS block_title
            FROM burials b
            LEFT JOIN plots p ON p.id = b.plot_id
            LEFT JOIN map_blocks mb ON mb.id = p.map_block_id
            WHERE b.burial_id = :burial_id AND b.is_active = 1
            LIMIT 1
        ");
        $this->db->bind(':burial_id', $burialId);
        return $this->db->single();
    }

    public function getRenewalHistory() {
        $this->db->query("
            SELECT r.*, u.username AS processed_by
            FROM renewals r
            LEFT JOIN users u ON u.id = r.processed_by_user_id
            ORDER BY r.payment_date DESC
        ");
        return $this->db->resultSet();
    }
    
    /**
     * Gumagawa ng renewal record at nag-uupdate ng burial.
     * Inalis na ang transaction para maging compatible sa iyong Database.php
     */
    public function createRenewal($data) {
        $today = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $txSeq = random_int(1, 999);
        $transactionId = 'REN-' . $today->format('Ymd') . '-' . str_pad((string)$txSeq, 3, '0', STR_PAD_LEFT);
        
        $this->db->query("
            INSERT INTO renewals (burial_id, transaction_id, previous_expiry_date, new_expiry_date, payment_amount, payment_date, payer_name, payer_email, receipt_email_status, processed_by_user_id)
            VALUES (:burial_id, :transaction_id, :prev_expiry, :new_expiry, :amount, :pay_date, :payer_name, :payer_email, :email_status, :user_id)
        ");
        $this->db->bind(':burial_id', $data['burial_id']);
        $this->db->bind(':transaction_id', $transactionId);
        $this->db->bind(':prev_expiry', $data['previous_expiry_date']);
        $this->db->bind(':new_expiry', $data['new_expiry_date']);
        $this->db->bind(':amount', $data['payment_amount']);
        $this->db->bind(':pay_date', $data['payment_date']);
        $this->db->bind(':payer_name', $data['payer_name']);
        $this->db->bind(':payer_email', $data['payer_email']);
        $this->db->bind(':email_status', $data['receipt_email_status']);
        $this->db->bind(':user_id', $data['processed_by_user_id']);
        
        if (!$this->db->execute()) {
            return false;
        }

        $this->db->query("
            UPDATE burials SET rental_date = :new_rental_date, expiry_date = :new_expiry 
            WHERE burial_id = :burial_id
        ");
        $this->db->bind(':new_rental_date', $data['new_rental_date']);
        $this->db->bind(':new_expiry', $data['new_expiry_date']);
        $this->db->bind(':burial_id', $data['burial_id']);

        if ($this->db->execute()) {
            return ['ok' => true, 'transaction_id' => $transactionId];
        } else {
            return false;
        }
    }
    
    public function updateEmailStatus($transactionId, $status) {
        $this->db->query("UPDATE renewals SET receipt_email_status = :status WHERE transaction_id = :tid");
        $this->db->bind(':status', $status);
        $this->db->bind(':tid', $transactionId);
        return $this->db->execute();
    }

public function vacatePlot($burialId, $plotId, $userId) {
        // Step 1: I-archive ang specific na burial record sa pamamagitan ng pag-set ng is_active = 0
        $this->db->query("UPDATE burials SET is_active = 0, updated_by_user_id = :user_id WHERE burial_id = :burial_id");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':burial_id', $burialId);
        if (!$this->db->execute()) {
            return false; // Huminto kung nabigo ang pag-archive
        }

        // Step 2: Bilangin kung may natitira pang active na libing sa parehong plot
        $this->db->query("SELECT COUNT(*) as active_count FROM burials WHERE plot_id = :plot_id AND is_active = 1");
        $this->db->bind(':plot_id', $plotId);
        $result = $this->db->single();
        $activeCount = $result ? (int)$result->active_count : 0;

        // Step 3: I-update lang ang status ng plot sa 'vacant' kung zero (0) na ang bilang ng active burials
        if ($activeCount === 0) {
            $this->db->query("UPDATE plots SET status = 'vacant' WHERE id = :plot_id");
            $this->db->bind(':plot_id', $plotId);
            return $this->db->execute(); // Ibalik ang resulta ng huling update
        }

        // Kung may natitira pang active burials, successful pa rin ang proseso pero hindi binabago ang status ng plot.
        return true;
    }
}