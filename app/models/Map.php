<?php
// app/models/Map.php

class Map {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getAllBlocks(){
        $this->db->query('SELECT mb.*, pl.modal_rows, pl.modal_cols FROM map_blocks as mb LEFT JOIN plot_layouts as pl ON mb.id = pl.map_block_id ORDER BY mb.id ASC');
        return $this->db->resultSet();
    }
    
    public function getBlockWithPlotsAndBurials($block_key){
        $this->db->query('SELECT mb.*, pl.modal_rows, pl.modal_cols FROM map_blocks as mb LEFT JOIN plot_layouts as pl ON mb.id = pl.map_block_id WHERE mb.block_key = :block_key');
        $this->db->bind(':block_key', $block_key);
        $block = $this->db->single();

        if($block && isset($block->id)){
            $this->db->query('
                SELECT 
                    p.*,
                    b.burial_id,
                    b.deceased_first_name,
                    b.deceased_middle_name,
                    b.deceased_last_name
                FROM plots p 
                LEFT JOIN burials b ON p.id = b.plot_id AND b.is_active = 1
                WHERE p.map_block_id = :block_id 
                ORDER BY p.id ASC
            ');
            $this->db->bind(':block_id', $block->id);
            $block->plots = $this->db->resultSet();
        } else {
            if ($block) { $block->plots = []; }
        }
        return $block;
    }

    public function updateBlock($data){
        $this->db->query('UPDATE map_blocks SET title = :title, offset_x = :offset_x, offset_y = :offset_y WHERE id = :id');
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':offset_x', $data['offset_x']);
        $this->db->bind(':offset_y', $data['offset_y']);
        if(!$this->db->execute()){ return false; }

        $this->db->query('INSERT INTO plot_layouts (map_block_id, modal_rows, modal_cols) VALUES (:id, :rows, :cols) ON DUPLICATE KEY UPDATE modal_rows = :rows, modal_cols = :cols');
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':rows', $data['modal_rows'] ?? 4);
        $this->db->bind(':cols', $data['modal_cols'] ?? 8);
        if(!$this->db->execute()){ return false; }

        $this->db->query('DELETE FROM plots WHERE map_block_id = :block_id');
        $this->db->bind(':block_id', $data['id']);
        $this->db->execute(); 

        $total_plots = ($data['modal_rows'] ?? 4) * ($data['modal_cols'] ?? 8);
        $this->db->query('INSERT INTO plots (map_block_id, plot_number, status) VALUES (:map_block_id, :plot_number, "vacant")');
        
        for ($i = 1; $i <= $total_plots; $i++) {
            $plot_number = $data['title'] . sprintf('%03d', $i);
            $this->db->bind(':map_block_id', $data['id']);
            $this->db->bind(':plot_number', $plot_number);
            if(!$this->db->execute()){ return false; }
        }
        
        return true;
    }
    
    public function updateBlockOffsets($block_id, $offset_x, $offset_y){
        $this->db->query('UPDATE map_blocks SET offset_x = :offset_x, offset_y = :offset_y WHERE id = :id');
        $this->db->bind(':id', $block_id);
        $this->db->bind(':offset_x', $offset_x);
        $this->db->bind(':offset_y', $offset_y);
        return $this->db->execute();
    }

    public function getVacantPlots() {
        $this->db->query("
            SELECT p.id, CONCAT(mb.title, ' - ', p.plot_number) AS full_plot_number 
            FROM plots p 
            JOIN map_blocks mb ON p.map_block_id = mb.id
            WHERE p.status = 'vacant'
        ");
        return $this->db->resultSet();
    }

    public function updatePlotStatus($plot_id, $status) {
        $this->db->query("
            UPDATE plots SET status = :status WHERE id = :plot_id
        ");
        $this->db->bind(':status', $status);
        $this->db->bind(':plot_id', $plot_id);
        return $this->db->execute();
    }
}