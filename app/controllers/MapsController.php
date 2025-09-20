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
  }
?>