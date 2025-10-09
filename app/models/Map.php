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
            // [MODIFIED] Added a subquery to get the active occupant count for each plot
            $this->db->query('
                SELECT 
                    p.*,
                    (SELECT COUNT(*) FROM burials WHERE plot_id = p.id AND is_active = 1) as occupant_count,
                    b.burial_id,
                    b.deceased_first_name,
                    b.deceased_middle_name,
                    b.deceased_last_name
                FROM plots p 
                LEFT JOIN burials b 
                    ON b.burial_id = (
                        SELECT bb.burial_id
                        FROM burials bb
                        WHERE bb.plot_id = p.id
                        ORDER BY 
                            bb.is_active DESC,
                            bb.burial_id DESC
                        LIMIT 1
                    )
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
// app/models/Map.php

// app/models/Map.php

public function updateBlock($data){
    $block_id = $data['id'];
    $new_title = $data['title'];
    $new_rows = $data['modal_rows'] ?? 4;
    $new_cols = $data['modal_cols'] ?? 8;
    $new_total_plots = $new_rows * $new_cols;

    // --- Step 1: Kunin ang LAHAT ng existing plots sa block na ito ---
    $this->db->query("SELECT id, plot_number, status FROM plots WHERE map_block_id = :block_id ORDER BY id ASC");
    $this->db->bind(':block_id', $block_id);
    $existing_plots = $this->db->resultSet();
    $current_plot_count = count($existing_plots);
    
    $occupied_count = 0;
    foreach($existing_plots as $p) {
        if ($p->status !== 'vacant') {
            $occupied_count++;
        }
    }

    // --- Step 2: Safety Check ---
    // Bago gawin ang kahit ano, siguraduhing kasya pa rin ang mga occupied plots sa bagong sukat.
    if ($new_total_plots < $occupied_count) {
        // Mag-return ng error. Hindi pwedeng paliitin ang block kung masisiksik ang mga nakalibing.
        return false; 
    }

    // --- Step 3: I-update ang block title at layout details ---
    $this->db->query('UPDATE map_blocks SET title = :title WHERE id = :id');
    $this->db->bind(':id', $block_id);
    $this->db->bind(':title', $new_title);
    if(!$this->db->execute()){ return false; }

    $this->db->query('INSERT INTO plot_layouts (map_block_id, modal_rows, modal_cols) VALUES (:id, :rows, :cols) ON DUPLICATE KEY UPDATE modal_rows = :rows, modal_cols = :cols');
    $this->db->bind(':id', $block_id);
    $this->db->bind(':rows', $new_rows);
    $this->db->bind(':cols', $new_cols);
    if(!$this->db->execute()){ return false; }

    // --- Step 4: I-update ang pangalan ng BAWAT existing plot ---
    $this->db->query("UPDATE plots SET plot_number = :plot_number WHERE id = :id");
    $sequence = 1;
    foreach($existing_plots as $plot){
        $new_plot_number = $new_title . sprintf('%03d', $sequence);
        $this->db->bind(':plot_number', $new_plot_number);
        $this->db->bind(':id', $plot->id);
        $this->db->execute();
        $sequence++;
    }

    // --- Step 5: I-handle ang pagbabago sa bilang ng plots (resizing) ---
    if ($new_total_plots > $current_plot_count) {
        // --- Kung lumaki ang block (magdagdag ng bagong plots) ---
        $plots_to_add = $new_total_plots - $current_plot_count;
        $this->db->query('INSERT INTO plots (map_block_id, plot_number, status) VALUES (:map_block_id, :plot_number, "vacant")');
        
        for ($i = 0; $i < $plots_to_add; $i++) {
            $new_plot_number = $new_title . sprintf('%03d', $sequence);
            $this->db->bind(':map_block_id', $block_id);
            $this->db->bind(':plot_number', $new_plot_number);
            $this->db->execute();
            $sequence++;
        }

    } elseif ($new_total_plots < $current_plot_count) {
        // --- Kung lumiit ang block (magbura ng sobrang VACANT plots) ---
        $plots_to_delete_count = $current_plot_count - $new_total_plots;
        
        // Kunin ang mga sobrang vacant plots, simula sa pinakamataas ang ID (pinakahuling ginawa)
        $this->db->query("SELECT id FROM plots WHERE map_block_id = :block_id AND status = 'vacant' ORDER BY id DESC LIMIT :limit");
        $this->db->bind(':block_id', $block_id);
        $this->db->bind(':limit', $plots_to_delete_count);
        $plots_to_delete = $this->db->resultSet();

        if(!empty($plots_to_delete)){
            $this->db->query("DELETE FROM plots WHERE id = :id");
            foreach($plots_to_delete as $plot){
                $this->db->bind(':id', $plot->id);
                $this->db->execute();
            }
        }
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
