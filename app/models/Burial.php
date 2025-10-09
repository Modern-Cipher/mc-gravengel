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
        $burialId = 'B-' . substr(strtoupper(bin2hex(random_bytes(4))), 0, 6);
        $today = new DateTime('now');
        $txSeq = random_int(1, 999);
        $transactionId = $today->format('Ymd') . '-' . str_pad((string)$txSeq, 3, '0', STR_PAD_LEFT);

        $this->db->query("
            INSERT INTO burials (
                plot_id, burial_id, transaction_id, parent_burial_id,
                deceased_first_name, deceased_middle_name, deceased_last_name, deceased_suffix,
                age, sex, date_born, date_died, cause_of_death,
                grave_level, grave_type,
                interment_full_name, interment_relationship, interment_contact_number, interment_address, interment_email,
                payment_amount, rental_date, expiry_date,
                requirements, created_by_user_id, is_active
            ) VALUES (
                :plot_id, :burial_id, :transaction_id, :parent_burial_id,
                :dfn, :dmn, :dln, :dsuf,
                :age, :sex, :born, :died, :cod,
                :glevel, :gtype,
                :ir_name, :ir_rel, :ir_contact, :ir_address, :ir_email,
                :pay, :rent, :exp,
                :reqs, :created_by, 1
            )
        ");

        $this->db->bind(':plot_id', (int)$d['plot_id']);
        $this->db->bind(':burial_id', $burialId);
        $this->db->bind(':transaction_id', $transactionId);
        $this->db->bind(':parent_burial_id', $d['parent_burial_id'] ?? null);

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
        $this->db->bind(':ir_email', ($d['interment_email'] && trim($d['interment_email']) !== '') ? $d['interment_email'] : null);
        $this->db->bind(':pay',  $d['payment_amount'] ?? 0);
        $this->db->bind(':rent', $d['rental_date'] ?: null);
        $this->db->bind(':exp',  $d['expiry_date'] ?: null);
        $this->db->bind(':reqs', $d['requirements'] ?? null);
        $this->db->bind(':created_by', $d['created_by_user_id'] ?? null);

        if (!$this->db->execute()) return false;

        if (is_null($d['parent_burial_id'])) {
            $this->db->query("UPDATE plots SET status = 'occupied' WHERE id = :pid");
            $this->db->bind(':pid', (int)$d['plot_id']);
            $this->db->execute();
        }

        return [
            'insert_id'      => $this->db->lastInsertId(),
            'burial_id'      => $burialId,
            'transaction_id' => $transactionId
        ];
    }

    public function updateBurial($data) {
        $this->db->query("
            UPDATE burials SET
                deceased_first_name = :deceased_first_name, deceased_middle_name = :deceased_middle_name,
                deceased_last_name  = :deceased_last_name, deceased_suffix = :deceased_suffix,
                age = :age, sex = :sex, date_born = :date_born, date_died = :date_died,
                cause_of_death = :cause_of_death, grave_level = :grave_level, grave_type = :grave_type,
                interment_full_name = :interment_full_name, interment_relationship = :interment_relationship,
                interment_contact_number = :interment_contact_number, interment_address = :interment_address,
                interment_email = :interment_email, payment_amount = :payment_amount,
                rental_date = :rental_date, expiry_date = :expiry_date, requirements = :requirements,
                updated_by_user_id = :updated_by_user_id
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
        $this->db->bind(':interment_email', ($data['interment_email'] && trim($data['interment_email']) !== '') ? $data['interment_email'] : null);
        $this->db->bind(':payment_amount', $data['payment_amount'] ?? 0);
        $this->db->bind(':rental_date',   $data['rental_date'] ?: null);
        $this->db->bind(':expiry_date',   $data['expiry_date'] ?: null);
        $this->db->bind(':requirements',  $data['requirements'] ?? null);
        $this->db->bind(':updated_by_user_id', $data['updated_by_user_id'] ?? null);

        return $this->db->execute();
    }

     public function getBurialsByPlot($plotId)
    {
        $sql = "
        SELECT
          b.burial_id,
          b.transaction_id,
          b.plot_id,
          p.plot_number,
          b.is_active,
          b.deceased_first_name, b.deceased_middle_name, b.deceased_last_name, b.deceased_suffix,
          b.date_born, b.date_died, b.age, b.sex, b.cause_of_death,
          b.interment_full_name, b.interment_relationship, b.interment_contact_number,
          b.interment_address, b.interment_email,
          b.payment_amount, b.rental_date, b.expiry_date,
          b.grave_level, b.grave_type, b.requirements,
          b.created_at
        FROM burials b
        JOIN plots p ON p.id = b.plot_id
        WHERE b.plot_id = :pid
        ORDER BY 
          CASE WHEN b.is_active = 1 THEN 0 ELSE 1 END,
          COALESCE(b.created_at, b.rental_date) DESC";
        $this->db->query($sql);
        $this->db->bind(':pid', $plotId);
        return $this->db->resultSet();
    }

        /** Single burial full details (for “Details” button) */
    public function getBurialById($burialId)
    {
        $sql = "
        SELECT
          b.*, p.plot_number
        FROM burials b
        LEFT JOIN plots p ON p.id = b.plot_id
        WHERE b.burial_id = :bid
        LIMIT 1";
        $this->db->query($sql);
        $this->db->bind(':bid', $burialId);
        return $this->db->single();
    }

    /* =========================================
       ARCHIVE / RESTORE / DELETE
       ========================================= */

    public function setActiveFlagByBurialId(string $burial_id, int $flag) {
        $this->db->query("UPDATE burials SET is_active = :f WHERE burial_id = :bid");
        $this->db->bind(':f',  $flag);
        $this->db->bind(':bid', $burial_id);
        return $this->db->execute();
    }
    public function archiveByBurialId(string $burial_id) { return $this->setActiveFlagByBurialId($burial_id, 0); }
    public function restoreByBurialId(string $burial_id) { return $this->setActiveFlagByBurialId($burial_id, 1); }

    public function deleteByBurialId($burial_id){
        $this->db->query("SELECT plot_id FROM burials WHERE burial_id = :bid LIMIT 1");
        $this->db->bind(':bid', $burial_id);
        $row = $this->db->single();
        $plotId = $row->plot_id ?? null;

        $this->db->query("DELETE FROM burials WHERE burial_id = :bid");
        $this->db->bind(':bid', $burial_id);
        $ok = $this->db->execute();

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

    public function countActive(): int {
        $this->db->query("SELECT COUNT(*) AS c FROM burials WHERE is_active = 1");
        $r = $this->db->single();
        return (int)($r->c ?? 0);
    }

    public function countExpired(): int {
        $this->db->query("SELECT COUNT(*) AS c FROM burials WHERE is_active = 1 AND expiry_date IS NOT NULL AND DATE(expiry_date) < CURDATE()");
        $r = $this->db->single();
        return (int)($r->c ?? 0);
    }

    public function countTodayTransactions(): int {
        $this->db->query("SELECT COUNT(*) AS c FROM burials WHERE DATE(rental_date) = CURDATE()");
        $r = $this->db->single();
        return (int)($r->c ?? 0);
    }

    /* =========================================
       CALENDAR & NOTIFICATIONS
       ========================================= */

    public function getExpiryEventsInRange(string $from, string $to) {
        $this->db->query("
            SELECT b.burial_id, b.expiry_date, b.interment_full_name, b.deceased_first_name, b.deceased_last_name,
                   b.grave_level, b.grave_type, mb.title AS block_title, p.plot_number
            FROM burials b JOIN plots p ON p.id = b.plot_id JOIN map_blocks mb ON mb.id = p.map_block_id
            WHERE b.is_active = 1 AND b.expiry_date IS NOT NULL AND DATE(b.expiry_date) BETWEEN :dfrom AND :dto
            ORDER BY b.expiry_date ASC
        ");
        $this->db->bind(':dfrom', $from);
        $this->db->bind(':dto',   $to);
        return $this->db->resultSet();
    }

    public function getExpiringWithinDays(int $days = 30) {
        $this->db->query("
            SELECT b.burial_id, b.interment_full_name, b.interment_email, b.expiry_date, mb.title AS block_title, p.plot_number
            FROM burials b JOIN plots p ON p.id = b.plot_id JOIN map_blocks mb ON mb.id = p.map_block_id
            WHERE b.is_active = 1 AND b.expiry_date IS NOT NULL AND TIMESTAMPDIFF(DAY, NOW(), b.expiry_date) = :days
            ORDER BY b.expiry_date ASC
        ");
        $this->db->bind(':days', $days);
        return $this->db->resultSet();
    }

    public function getExpiredToday() {
        $this->db->query("
            SELECT b.burial_id, b.interment_full_name, b.interment_email, b.expiry_date, mb.title AS block_title, p.plot_number
            FROM burials b JOIN plots p ON p.id = b.plot_id JOIN map_blocks mb ON mb.id = p.map_block_id
            WHERE b.is_active = 1 AND b.expiry_date IS NOT NULL AND DATE(b.expiry_date) = CURDATE()
            ORDER BY b.expiry_date ASC
        ");
        return $this->db->resultSet();
    }

    /* =========================================
       PUBLIC & MISC
       ========================================= */

    public function findPublicByBurialId($burialId) {
        $sql = "SELECT b.burial_id, CONCAT(COALESCE(b.deceased_first_name,''),' ', COALESCE(b.deceased_last_name,'')) AS deceased_full_name,
                       b.date_born, b.date_died, b.grave_level, b.grave_type, p.plot_number, mb.title AS block_title
                FROM burials b JOIN plots p ON p.id = b.plot_id JOIN map_blocks mb ON mb.id = p.map_block_id
                WHERE b.burial_id = :bid AND b.is_active = 1 LIMIT 1";
        $this->db->query($sql);
        $this->db->bind(':bid', $burialId);
        return $this->db->single();
    }

    /* =========================================
       NEW DASHBOARD CHART METHODS
       ========================================= */
    
    public function countNewRentalsThisMonth(): int {
        $this->db->query("SELECT COUNT(*) AS count FROM burials WHERE YEAR(rental_date) = YEAR(CURDATE()) AND MONTH(rental_date) = MONTH(CURDATE()) AND is_active = 1");
        $row = $this->db->single();
        return (int)($row->count ?? 0);
    }
    
    public function countExpiringSoon(int $days = 30): int {
        $this->db->query("SELECT COUNT(*) AS count FROM burials WHERE is_active = 1 AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)");
        $this->db->bind(':days', $days);
        $row = $this->db->single();
        return (int)($row->count ?? 0);
    }

    public function countAllExpired(): int {
        $this->db->query("SELECT COUNT(*) AS count FROM burials WHERE is_active = 1 AND expiry_date < CURDATE()");
        $row = $this->db->single();
        return (int)($row->count ?? 0);
    }
    
    public function getDailyTransactionTotals(int $days = 7): array {
        $dateList = []; for ($i = 0; $i < $days; $i++) { $dateList[] = date('Y-m-d', strtotime("-$i days")); }
        $dateList = array_reverse($dateList);

        $this->db->query("
            SELECT CAST(transaction_date AS DATE) as 'date', SUM(amount) as 'total' FROM (
                SELECT rental_date as transaction_date, payment_amount as amount FROM burials WHERE rental_date >= :start_date
                UNION ALL
                SELECT payment_date as transaction_date, payment_amount as amount FROM renewals WHERE payment_date >= :start_date
            ) AS transactions GROUP BY CAST(transaction_date AS DATE)
        ");
        $this->db->bind(':start_date', date('Y-m-d', strtotime("-$days days")));
        $results = $this->db->resultSet();

        $dailyTotals = []; $resultMap = [];
        foreach ($results as $row) { $resultMap[$row->date] = (float)$row->total; }
        foreach ($dateList as $date) { $dailyTotals[$date] = $resultMap[$date] ?? 0; }
        return $dailyTotals;
    }
    
    /* =========================================
       NEW MULTI-OCCUPANCY METHODS
       ========================================= */

    public function getAllPlotsForDropdown() {
        // INAYOS ANG SYNTAX: $this->db
        $this->db->query("
            SELECT p.id, p.status, p.plot_number, mb.title AS block_title
            FROM plots p
            JOIN map_blocks mb ON mb.id = p.map_block_id
            ORDER BY p.status, mb.title, p.plot_number
        ");
        return $this->db->resultSet();
    }

    public function getPrimaryBurialForPlot($plot_id) {
        // INAYOS ANG SYNTAX: $this->db
        $this->db->query("
            SELECT burial_id
            FROM burials
            WHERE plot_id = :plot_id AND parent_burial_id IS NULL AND is_active = 1
            LIMIT 1
        ");
        $this->db->bind(':plot_id', $plot_id);
        return $this->db->single();
    }



    /**
     * [BAGO] Kinukuha ang LAHAT ng active occupants para sa isang plot ID.
     */
 public function getActiveOccupantsByPlotId($plot_id) {
        $this->db->query("
            SELECT 
                burial_id,
                deceased_first_name,
                deceased_last_name,
                interment_full_name,      -- ADDED
                interment_contact_number,
                interment_email,          -- ADDED
                date_born,
                date_died,
                expiry_date,
                grave_type,
                parent_burial_id
            FROM burials
            WHERE plot_id = :plot_id AND is_active = 1
            ORDER BY created_at ASC
        ");
        $this->db->bind(':plot_id', $plot_id);
        return $this->db->resultSet();
    }

    /**
     * [CORRECTED] Kinukuha ang mga detalye ng plot para sa Add Burial Form.
     * Kino-compute ang grave_level para sa vacant, at kinukuha ang data sa primary burial para sa occupied.
     */
    public function getDetailsForBurialForm($plot_id) {
        // 1. Kunin ang status at block_id ng plot
        $this->db->query("SELECT status, map_block_id FROM plots WHERE id = :plot_id");
        $this->db->bind(':plot_id', $plot_id);
        $plotInfo = $this->db->single();

        if (!$plotInfo) {
            return null;
        }

        if ($plotInfo->status === 'occupied') {
            // 2a. Para sa occupied plots, kunin ang detalye mula sa primary burial
            $this->db->query("
                SELECT grave_level, grave_type
                FROM burials
                WHERE plot_id = :plot_id AND parent_burial_id IS NULL AND is_active = 1
                LIMIT 1
            ");
            $this->db->bind(':plot_id', $plot_id);
            $burialInfo = $this->db->single();
            return [
                'status' => 'occupied',
                'grave_level' => $burialInfo->grave_level ?? null,
                'grave_type' => $burialInfo->grave_type ?? null,
            ];
        } else { // Para sa 'vacant'
            // 2b. Para sa vacant plots, i-calculate ang grave_level base sa layout
            $this->db->query("
                SELECT pl.modal_cols
                FROM plot_layouts pl
                WHERE pl.map_block_id = :map_block_id
            ");
            $this->db->bind(':map_block_id', $plotInfo->map_block_id);
            $layout = $this->db->single();

            if (!$layout || !$layout->modal_cols) {
                return ['status' => 'vacant', 'grave_level' => null]; // Hindi ma-calculate
            }

            $this->db->query("SELECT id FROM plots WHERE map_block_id = :map_block_id ORDER BY id ASC");
            $this->db->bind(':map_block_id', $plotInfo->map_block_id);
            $allPlotsInBlock = $this->db->resultSet();

            $allPlotIds = array_map(function($p) { return $p->id; }, $allPlotsInBlock);
            $plotIndex = array_search($plot_id, $allPlotIds);

            if ($plotIndex === false) {
                 return ['status' => 'vacant', 'grave_level' => null];
            }

            $cols = (int)$layout->modal_cols;
            
            // --- START OF FIX ---
            // The row index based on the natural order of plots from the database
            // IS the correct index for the grave level array.
            // Row 0 (bottom row on map) = Level A, Row 1 = Level B, etc.
            $rowIndex = floor($plotIndex / $cols);
            $graveLevelIndex = $rowIndex;
            // --- END OF FIX ---

            $graveLevels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

            return [
                'status' => 'vacant',
                'grave_level' => $graveLevels[$graveLevelIndex] ?? 'N/A',
                'grave_type' => null
            ];
        }
    }

}