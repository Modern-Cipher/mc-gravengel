<?php

class Burial {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllBurialRecords() {
        $this->db->query("
            SELECT b.*, p.plot_number, mb.title as block_title
            FROM burials b
            JOIN plots p ON b.plot_id = p.id
            JOIN map_blocks mb ON p.map_block_id = mb.id
            WHERE b.is_active = 1
            ORDER BY b.created_at DESC
        ");
        return $this->db->resultSet();
    }
    
public function findByBurialId($burial_id) {
        $this->db->query("
            SELECT b.*, p.plot_number, mb.title as block_title
            FROM burials b
            JOIN plots p ON b.plot_id = p.id
            JOIN map_blocks mb ON p.map_block_id = mb.id
            WHERE b.burial_id = :burial_id
            AND b.is_active = 1
        ");
        $this->db->bind(':burial_id', $burial_id);
        return $this->db->single();
    }
    
    public function getBurialRecordWithCreator($burial_id) {
        $this->db->query("
            SELECT b.*, u.first_name, u.last_name
            FROM burials b
            LEFT JOIN users u ON b.created_by_user_id = u.id
            WHERE b.burial_id = :burial_id
        ");
        $this->db->bind(':burial_id', $burial_id);
        return $this->db->single();
    }
    
    public function addBurial($data) {
        $this->db->query("
            INSERT INTO burials (
                plot_id, burial_id, deceased_first_name, deceased_middle_name, deceased_last_name, 
                deceased_suffix, age, sex, date_born, date_died, cause_of_death, grave_level, grave_type, 
                interment_full_name, interment_relationship, interment_contact_number, interment_address, 
                payment_amount, rental_date, expiry_date, requirements, created_by_user_id
            ) VALUES (
                :plot_id, :burial_id, :deceased_first_name, :deceased_middle_name, :deceased_last_name, 
                :deceased_suffix, :age, :sex, :date_born, :date_died, :cause_of_death, :grave_level, :grave_type, 
                :interment_full_name, :interment_relationship, :interment_contact_number, :interment_address, 
                :payment_amount, :rental_date, :expiry_date, :requirements, :created_by_user_id
            )
        ");

        $date_born = $data['date_born'] ? (new DateTime($data['date_born']))->format('Y-m-d') : NULL;
        $date_died = $data['date_died'] ? (new DateTime($data['date_died']))->format('Y-m-d') : NULL;
        $rental_date = $data['rental_date'] ? (new DateTime($data['rental_date']))->format('Y-m-d H:i:s') : NULL;
        $expiry_date = $data['expiry_date'] ? (new DateTime($data['expiry_date']))->format('Y-m-d H:i:s') : NULL;

        $this->db->bind(':plot_id', $data['plot_id']);
        $this->db->bind(':burial_id', $data['burial_id']);
        $this->db->bind(':deceased_first_name', $data['deceased_first_name']);
        $this->db->bind(':deceased_middle_name', $data['deceased_middle_name']);
        $this->db->bind(':deceased_last_name', $data['deceased_last_name']);
        $this->db->bind(':deceased_suffix', $data['deceased_suffix']);
        $this->db->bind(':age', $data['age']);
        $this->db->bind(':sex', $data['sex']);
        $this->db->bind(':date_born', $date_born);
        $this->db->bind(':date_died', $date_died);
        $this->db->bind(':cause_of_death', $data['cause_of_death']);
        $this->db->bind(':grave_level', $data['grave_level']);
        $this->db->bind(':grave_type', $data['grave_type']);
        $this->db->bind(':interment_full_name', $data['interment_full_name']);
        $this->db->bind(':interment_relationship', $data['interment_relationship']);
        $this->db->bind(':interment_contact_number', $data['interment_contact_number']);
        $this->db->bind(':interment_address', $data['interment_address']);
        $this->db->bind(':payment_amount', $data['payment_amount']);
        $this->db->bind(':rental_date', $rental_date);
        $this->db->bind(':expiry_date', $expiry_date);
        $this->db->bind(':requirements', $data['requirements']);
        $this->db->bind(':created_by_user_id', $data['created_by_user_id']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    public function updateBurial($data) {
        $this->db->query("
            UPDATE burials SET
                deceased_first_name = :deceased_first_name,
                deceased_middle_name = :deceased_middle_name,
                deceased_last_name = :deceased_last_name,
                deceased_suffix = :deceased_suffix,
                age = :age,
                sex = :sex,
                date_born = :date_born,
                date_died = :date_died,
                cause_of_death = :cause_of_death,
                grave_level = :grave_level,
                grave_type = :grave_type,
                interment_full_name = :interment_full_name,
                interment_relationship = :interment_relationship,
                interment_contact_number = :interment_contact_number,
                interment_address = :interment_address,
                payment_amount = :payment_amount,
                rental_date = :rental_date,
                expiry_date = :expiry_date,
                requirements = :requirements,
                updated_by_user_id = :updated_by_user_id
            WHERE burial_id = :burial_id
        ");
        
        $rental_date = $data['rental_date'] ? (new DateTime($data['rental_date']))->format('Y-m-d H:i:s') : NULL;
        $expiry_date = $data['expiry_date'] ? (new DateTime($data['expiry_date']))->format('Y-m-d H:i:s') : NULL;
        
        $this->db->bind(':burial_id', $data['burial_id']);
        $this->db->bind(':deceased_first_name', $data['deceased_first_name']);
        $this->db->bind(':deceased_middle_name', $data['deceased_middle_name']);
        $this->db->bind(':deceased_last_name', $data['deceased_last_name']);
        $this->db->bind(':deceased_suffix', $data['deceased_suffix']);
        $this->db->bind(':age', $data['age']);
        $this->db->bind(':sex', $data['sex']);
        $this->db->bind(':date_born', $data['date_born']);
        $this->db->bind(':date_died', $data['date_died']);
        $this->db->bind(':cause_of_death', $data['cause_of_death']);
        $this->db->bind(':grave_level', $data['grave_level']);
        $this->db->bind(':grave_type', $data['grave_type']);
        $this->db->bind(':interment_full_name', $data['interment_full_name']);
        $this->db->bind(':interment_relationship', $data['interment_relationship']);
        $this->db->bind(':interment_contact_number', $data['interment_contact_number']);
        $this->db->bind(':interment_address', $data['interment_address']);
        $this->db->bind(':payment_amount', $data['payment_amount']);
        $this->db->bind(':rental_date', $rental_date);
        $this->db->bind(':expiry_date', $expiry_date);
        $this->db->bind(':requirements', $data['requirements']);
        $this->db->bind(':updated_by_user_id', $data['updated_by_user_id']);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function lastInsertId() {
        return $this->db->lastInsertId();
    }
}