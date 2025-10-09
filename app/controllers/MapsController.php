<?php
  class MapsController extends Controller {
    private $mapModel;

    public function __construct(){
      $this->mapModel = $this->model('Map');
    }

    public function index(){
        $blocks = $this->mapModel->getAllBlocks();
        $data = [ 'blocks' => $blocks ];
        $this->view('maps/index', $data);
    }

    public function manage(){
        $blocks = $this->mapModel->getAllBlocks();
        $data = [ 'title' => 'Manage Map Blocks', 'blocks' => $blocks ];
        $this->view('maps/manage', $data);
    }

    public function updateBlock() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $data = [
                'id' => $_POST['id'],
                'title' => trim($_POST['title']),
                'offset_x' => (int)$_POST['offset_x'],
                'offset_y' => (int)$_POST['offset_y'],
                'modal_rows' => (int)$_POST['modal_rows'],
                'modal_cols' => (int)$_POST['modal_cols']
            ];

            if ($this->mapModel->updateBlock($data)) {
              $_SESSION['flash_message'] = 'Block details saved successfully!';
              $_SESSION['flash_type'] = 'success';
              redirect('maps/manage');
            } else {
              die('Something went wrong');
            }
        } else {
            redirect('maps');
        }
    }

public function getBlockDetails($block_key = ''){
        header('Content-Type: application/json');
        if(empty($block_key)){
            echo json_encode(['error' => 'No block key provided']);
            return;
        }
        $blockData = $this->mapModel->getBlockWithPlotsAndBurials($block_key);
        echo json_encode($blockData);
    }

    public function saveCalibration() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $json = file_get_contents('php://input');
            $postData = json_decode($json);

            $block_id = $postData->block_id ?? 0;
            $offset_x = $postData->offset_x ?? 0;
            $offset_y = $postData->offset_y ?? 0;

            if (empty($block_id)) {
                echo json_encode(['success' => false, 'message' => 'Invalid data.']);
                return;
            }

            if ($this->mapModel->updateBlockOffsets($block_id, $offset_x, $offset_y)) {
                echo json_encode(['success' => true, 'message' => 'Calibration saved!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save calibration.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        }
    }


// GET /maps/publicBurial/{burialId}
public function publicBurial($burialId = null) {
    header('Content-Type: application/json');
    try {
        if (!$burialId) { echo json_encode(['ok'=>false,'error'=>'Missing burial_id']); return; }
        $model = $this->model('Burial');
        $row   = $model->findPublicByBurialId($burialId);
        if ($row) {
            echo json_encode(['ok'=>true, 'data'=>$row]);
        } else {
            echo json_encode(['ok'=>false,'error'=>'Not found']);
        }
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['ok'=>false,'error'=>'Server error']);
    }
}



// GET /maps/getPlotBurials/{plotId}
public function getPlotBurials($plotId = 0)
{
    header('Content-Type: application/json; charset=utf-8');

    if (!isset($plotId) || !is_numeric($plotId) || $plotId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Invalid plot id']); 
        return;
    }

    try {
        $burialModel = $this->model('Burial');
        $rows = $burialModel->getBurialsByPlot((int)$plotId); // see Burial model method below
        echo json_encode(['ok' => true, 'burials' => $rows]);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Server error']);
    }
}


  }
?>