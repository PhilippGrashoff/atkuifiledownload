<?php declare(strict_types=1);

namespace atkuifiledownload;

use Atk4\Core\AppScopeTrait;
use Atk4\Ui\App;
use fileforatk\File;

class FileDownload
{
    use AppScopeTrait;

    public string $paramNameForCryptID = 'fileid';
    protected string $currentFileName = '';
    protected string $currentFilePath = '';

    protected string $fileClassName = File::class;



    public function __construct(App $app)
    {
        $this->setApp($app);
    }

    /**
     * cuurently based on fileforatk\File regarding in which fields cryptic id, filename and path to file are stored.
     */
    public function sendFile()
    {
        if (isset($_REQUEST[$this->paramNameForCryptID])) {
            $file = new $this->fileClassName($this->getApp()->db);
            $file->tryLoadBy('crypt_id', $_REQUEST[$this->paramNameForCryptID]);
            if (!$file->loaded()) {
                $this->_failure();
                return;
            }
            $this->currentFilePath = $file->getFullFilePath();
            $this->currentFileName = $file->get('value');
            $this->_sendFile();
        } else {
            $this->_failure(); //TODO sensible error code for parameter missing
        }
    }

    protected function _failure(int $errorCode = 404): void //todo error message
    {
        http_response_code($errorCode);
        //Todo exit missing
    }

    protected function _sendFile()
    {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"" . $this->currentFileName . "\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($this->currentFilePath));

        @readfile($this->currentFilePath);
        //Todo exit missing
    }
}