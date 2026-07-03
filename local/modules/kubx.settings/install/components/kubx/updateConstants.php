
<?php
header('Content-Type: application/json; charset=utf-8');

$result = 'fail';

$scriptPath = $_SERVER['DOCUMENT_ROOT'].'/local/console.php';

if (file_exists($scriptPath)) {
    $command = escapeshellcmd("php ".$scriptPath)." Constants 2>&1";
    $output = [];
    $returnCode = 0;

    $res = exec($command, $output, $returnCode);
    if ($returnCode === 0) {
        $result = 'success';
    } else {
        $result = 'fail';
    }
}
echo json_encode(['result' => $result], JSON_UNESCAPED_UNICODE);
exit();
?>
