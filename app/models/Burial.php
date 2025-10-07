<?php
/**
 * app/models/Burial.php
 */
class Burial {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /* =========================================
       LISTS / FETCH
       ========================================= */

    // Active burials (is_active = 1)
    public function getAllBurialRecords() {
        $this->db->query("
            SELECT b.*,
                   p.plot_number,
                   mb.title AS block_title
            FROM burials b
            JOIN plots p       ON p.id = b.plot_id
            JOIN map_blocks mb ON mb.id = p.map_block_id
            WHERE b.is_active = 1
            ORDER BY b.created_at DESC
        ");
        return $this->db->resultSet();
    }

    // Archived burials (is_active = 0)
    public function getAllBurialRecordsArchived() {
        $this->db->query("
            SELECT b.*,
                   p.plot_number,
                   mb.title AS block_title
            FROM burials b
            JOIN plots p       ON p.id = b.plot_id
            JOIN map_blocks mb ON mb.id = p.map_block_id
            WHERE b.is_active = 0
            ORDER BY b.created_at DESC
        ");
        return $this->db->resultSet();
    }

    // Active only by burial_id (kept for compatibility)
    public function findByBurialId($burial_id) {
        $this->db->query("
            SELECT b.*,
                   p.plot_number,
                   mb.title AS block_title
            FROM burials b
            JOIN plots p       ON p.id = b.plot_id
            JOIN map_blocks mb ON mb.id = p.map_block_id
            WHERE b.burial_id = :burial_id AND b.is_active = 1
            LIMIT 1
        ");
        $this->db->bind(':burial_id', $burial_id);
        return $this->db->single();
    }

    // ANY (active or archived) by burial_id – ginagamit sa view/print/details
    public function findAnyByBurialId($burial_id) {
        $this->db->query("
            SELECT b.*,
                   p.plot_number,
                   mb.title AS block_title
            FROM burials b
            JOIN plots p       ON p.id = b.plot_id
            JOIN map_blocks mb ON mb.id = p.map_block_id
            WHERE b.burial_id = :burial_id
            LIMIT 1
        ");
        $this->db->bind(':burial_id', $burial_id);
        return $this->db->single();
    }

    // 👉 Needed by AdminController->addBurial() (GET branch)
    public function getVacantPlotsFromPlots() {
        $this->db->query("
            SELECT p.id,
                   p.plot_number,
                   CONCAT(mb.title, ' ', p.plot_number) AS label
            FROM plots p
            JOIN map_blocks mb ON mb.id = p.map_block_id
            WHERE p.status = 'vacant'
            ORDER BY mb.title, p.plot_number
        ");
        return $this->db->resultSet();
    }

    /* =========================================
       CREATE / UPDATE
       ========================================= */

    public function create($d) {
        // Generate readable IDs
        $burialId = 'B-' . substr(strtoupper(bin2hex(random_bytes(4))), 0, 6);
        $today = new DateTime('now');
        $txSeq = random_int(1, 999);
        $transactionId = $today->format('Ymd') . '-' . str_pad((string)$txSeq, 3, '0', STR_PAD_LEFT);

        $this->db->query("
            INSERT INTO burials (
                plot_id, burial_id, transaction_id,
                deceased_first_name, deceased_middle_name, deceased_last_name, deceased_suffix,
                age, sex, date_born, date_died, cause_of_death,
                grave_level, grave_type,
                interment_full_name, interment_relationship, interment_contact_number, interment_address, interment_email,
                payment_amount, rental_date, expiry_date,
                requirements, created_by_user_id, is_active
            ) VALUES (
                :plot_id, :burial_id, :transaction_id,
                :dfn, :dmn, :dln, :dsuf,
                :age, :sex, :born, :died, :cod,
                :glevel, :gtype,
                :ir_name, :ir_rel, :ir_contact, :ir_address, :ir_email,
                :pay, :rent, :exp,
                :reqs, :created_by, 1
            )
        ");

        $this->db->bind(':plot_id',   (int)$d['plot_id']);
        $this->db->bind(':burial_id', $burialId);
        $this->db->bind(':transaction_id', $transactionId);

        $this->db->bind(':dfn',  $d['deceased_first_name']);
        $this->db->bind(':dmn',  $d['deceased_middle_name'] ?? null);
        $this->db->bind(':dln',  $d['deceased_last_name']);
        $this->db->bind(':dsuf', $d['deceased_suffix'] ?? null);
        $this->db->bind(':age',  $d['age'] ?? null);
        $this->db->bind(':sex',  $d['sex'] ?? null);

        $this->db->bind(':born', $d['date_born'] ?: null);
        $this->db->bind(':died', $d['date_died'] ?: null);
        $this->db->bind(':cod',  $d['cause_of_death'] ?? null);

        $this->db->bind(':glevel', $d['grave_level'] ?? null);
        $this->db->bind(':gtype',  $d['grave_type'] ?? null);

        $this->db->bind(':ir_name',    $d['interment_full_name']);
        $this->db->bind(':ir_rel',     $d['interment_relationship'] ?? null);
        $this->db->bind(':ir_contact', $d['interment_contact_number'] ?? null);
        $this->db->bind(':ir_address', $d['interment_address'] ?? null);

        // NEW: email (NULL kung empty string)
        $email = isset($d['interment_email']) ? trim((string)$d['interment_email']) : null;
        $email = ($email === '') ? null : $email;
        $this->db->bind(':ir_email', $email);

        $this->db->bind(':pay',  $d['payment_amount'] ?? 0);
        $this->db->bind(':rent', $d['rental_date'] ?: null);
        $this->db->bind(':exp',  $d['expiry_date'] ?: null);

        $this->db->bind(':reqs', $d['requirements'] ?? null);
        $this->db->bind(':created_by', $d['created_by_user_id'] ?? null);

        $ok = $this->db->execute();
        if (!$ok) return false;

        // Mark plot occupied on CREATE
        $this->db->query("UPDATE plots SET status = 'occupied' WHERE id = :pid");
        $this->db->bind(':pid', (int)$d['plot_id']);
        $this->db->execute();

        return [
            'insert_id'      => $this->db->lastInsertId(),
            'burial_id'      => $burialId,
            'transaction_id' => $transactionId
        ];
    }

    public function updateBurial($data) {
        $this->db->query("
            UPDATE burials SET
                deceased_first_name = :deceased_first_name,
                deceased_middle_name = :deceased_middle_name,
                deceased_last_name  = :deceased_last_name,
                deceased_suffix     = :deceased_suffix,
                age                 = :age,
                sex                 = :sex,
                date_born           = :date_born,
                date_died           = :date_died,
                cause_of_death      = :cause_of_death,
                grave_level         = :grave_level,
                grave_type          = :grave_type,
                interment_full_name = :interment_full_name,
                interment_relationship = :interment_relationship,
                interment_contact_number = :interment_contact_number,
                interment_address    = :interment_address,
                interment_email      = :interment_email,
                payment_amount       = :payment_amount,
                rental_date          = :rental_date,
                expiry_date          = :expiry_date,
                requirements         = :requirements,
                updated_by_user_id   = :updated_by_user_id
            WHERE burial_id = :burial_id
        ");

        $this->db->bind(':burial_id', $data['burial_id']);
        $this->db->bind(':deceased_first_name', $data['deceased_first_name']);
        $this->db->bind(':deceased_middle_name', $data['deceased_middle_name'] ?? null);
        $this->db->bind(':deceased_last_name',  $data['deceased_last_name']);
        $this->db->bind(':deceased_suffix',     $data['deceased_suffix'] ?? null);
        $this->db->bind(':age',   $data['age'] ?? null);
        $this->db->bind(':sex',   $data['sex'] ?? null);
        $this->db->bind(':date_born', $data['date_born'] ?: null);
        $this->db->bind(':date_died', $data['date_died'] ?: null);
        $this->db->bind(':cause_of_death', $data['cause_of_death'] ?? null);
        $this->db->bind(':grave_level', $data['grave_level'] ?? null);
        $this->db->bind(':grave_type',  $data['grave_type'] ?? null);
        $this->db->bind(':interment_full_name',     $data['interment_full_name']);
        $this->db->bind(':interment_relationship',  $data['interment_relationship'] ?? null);
        $this->db->bind(':interment_contact_number',$data['interment_contact_number'] ?? null);
        $this->db->bind(':interment_address',       $data['interment_address'] ?? null);

        // NEW: email (NULL kung empty string)
        $uEmail = isset($data['interment_email']) ? trim((string)$data['interment_email']) : null;
        $uEmail = ($uEmail === '') ? null : $uEmail;
        $this->db->bind(':interment_email', $uEmail);

        $this->db->bind(':payment_amount', $data['payment_amount'] ?? 0);
        $this->db->bind(':rental_date',   $data['rental_date'] ?: null);
        $this->db->bind(':expiry_date',   $data['expiry_date'] ?: null);
        $this->db->bind(':requirements',  $data['requirements'] ?? null);
        $this->db->bind(':updated_by_user_id', $data['updated_by_user_id'] ?? null);

        return $this->db->execute();
    }

    /* =========================================
       ARCHIVE / RESTORE / DELETE
       ========================================= */

    // Flip is_active 0/1 by burial_id
    public function setActiveFlagByBurialId(string $burial_id, int $flag) {
        $this->db->query("UPDATE burials SET is_active = :f WHERE burial_id = :bid");
        $this->db->bind(':f',  $flag);
        $this->db->bind(':bid', $burial_id);
        return $this->db->execute();
    }
    public function archiveByBurialId(string $burial_id) { return $this->setActiveFlagByBurialId($burial_id, 0); }
    public function restoreByBurialId(string $burial_id) { return $this->setActiveFlagByBurialId($burial_id, 1); }

    // DELETE burial record; (optional) free the plot
    public function deleteByBurialId($burial_id){
        // get plot first
        $this->db->query("SELECT plot_id FROM burials WHERE burial_id = :bid LIMIT 1");
        $this->db->bind(':bid', $burial_id);
        $row = $this->db->single();
        $plotId = $row->plot_id ?? null;

        // delete
        $this->db->query("DELETE FROM burials WHERE burial_id = :bid");
        $this->db->bind(':bid', $burial_id);
        $ok = $this->db->execute();

        // free plot only after delete (NOT on archive)
        if ($ok && $plotId) {
            $this->db->query("UPDATE plots SET status = 'vacant' WHERE id = :pid");
            $this->db->bind(':pid', (int)$plotId);
            $this->db->execute();
        }
        return $ok;
    }

    /* =========================================
       DASHBOARD COUNTS
       ========================================= */

    // Active burials count
    public function countActive(): int {
        $this->db->query("SELECT COUNT(*) AS c FROM burials WHERE is_active = 1");
        $r = $this->db->single();
        return (int)($r->c ?? 0);
    }

    // Expired rentals count (active only with expiry_date < today)
    public function countExpired(): int {
        $this->db->query("
            SELECT COUNT(*) AS c
            FROM burials
            WHERE is_active = 1
              AND expiry_date IS NOT NULL
              AND DATE(expiry_date) < CURDATE()
        ");
        $r = $this->db->single();
        return (int)($r->c ?? 0);
    }

    // Today's transactions count — gamit ang rental_date
    public function countTodayTransactions(): int {
        $this->db->query("
            SELECT COUNT(*) AS c
            FROM burials
            WHERE DATE(rental_date) = CURDATE()
        ");
        $r = $this->db->single();
        return (int)($r->c ?? 0);
    }

    /* =========================================
       CALENDAR EVENTS (EXPIRY)
       ========================================= */

    public function getExpiryEventsInRange(string $from, string $to) {
        $this->db->query("
            SELECT
                b.burial_id,
                b.expiry_date,
                b.interment_full_name,
                b.deceased_first_name,
                b.deceased_last_name,
                b.grave_level,
                b.grave_type,
                mb.title        AS block_title,
                p.plot_number
            FROM burials b
            JOIN plots p       ON p.id = b.plot_id
            JOIN map_blocks mb ON mb.id = p.map_block_id
            WHERE b.is_active = 1
              AND b.expiry_date IS NOT NULL
              AND DATE(b.expiry_date) BETWEEN :dfrom AND :dto
            ORDER BY b.expiry_date ASC
        ");
        $this->db->bind(':dfrom', $from);
        $this->db->bind(':dto',   $to);
        return $this->db->resultSet();
    }

    /* =========================================
       NOTIFICATIONS QUERIES
       ========================================= */

    /**
     * 30-day warning, time-aware (exactly N*24 hours from now).
     * Returns active burials with plot and block info.
     */
    public function getExpiringWithinDays(int $days = 30) {
        $this->db->query("
            SELECT 
                b.burial_id,
                b.interment_full_name,
                b.interment_email,
                b.expiry_date,
                mb.title       AS block_title,
                p.plot_number
            FROM burials b
            JOIN plots p       ON p.id = b.plot_id
            JOIN map_blocks mb ON mb.id = p.map_block_id
            WHERE b.is_active = 1
              AND b.expiry_date IS NOT NULL
              AND TIMESTAMPDIFF(DAY, NOW(), b.expiry_date) = :days
            ORDER BY b.expiry_date ASC
        ");
        $this->db->bind(':days', $days);
        return $this->db->resultSet();
    }

    /**
     * Expired today (date-based), active burials only.
     */
    public function getExpiredToday() {
        $this->db->query("
            SELECT 
                b.burial_id,
                b.interment_full_name,
                b.interment_email,
                b.expiry_date,
                mb.title       AS block_title,
                p.plot_number
            FROM burials b
            JOIN plots p       ON p.id = b.plot_id
            JOIN map_blocks mb ON mb.id = p.map_block_id
            WHERE b.is_active = 1
              AND b.expiry_date IS NOT NULL
              AND DATE(b.expiry_date) = CURDATE()
            ORDER BY b.expiry_date ASC
        ");
        return $this->db->resultSet();
    }



public function findPublicByBurialId($burialId) {
    $sql = "SELECT 
                b.burial_id,
                CONCAT(COALESCE(b.deceased_first_name,''),' ',
                       COALESCE(b.deceased_last_name,'')) AS deceased_full_name,
                b.date_born, b.date_died,
                b.grave_level, b.grave_type,
                p.plot_number,
                mb.title AS block_title
            FROM burials b
            JOIN plots p      ON p.id = b.plot_id
            JOIN map_blocks mb ON mb.id = p.map_block_id
            WHERE b.burial_id = :bid AND b.is_active = 1
            LIMIT 1";
    $this->db->query($sql);
    $this->db->bind(':bid', $burialId);
    return $this->db->single();
}



    
}
