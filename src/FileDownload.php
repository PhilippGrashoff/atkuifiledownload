<?php declare(strict_types=1);

namespace atkuifiledownload;

use Atk4\Core\AppScopeTrait;
use Atk4\Core\DiContainerTrait;
use Atk4\Core\Exception;
use Atk4\Data\Model;
use Atk4\Ui\App;
use fileforatk\File;

class FileDownload
{
    use AppScopeTrait;
    use DiContainerTrait;

    public string $paramNameForCryptId = 'fileid';
    public string $modelIdField = 'crypt_id';
    protected string $fileName = '';
    protected string $filePath = '';

    protected string $fileClassName = File::class;
    protected Model $file; //Only generic Model on purpose in case fileforatk\file is not used

    public function __construct(App $app, array $defaults = [])
    {
        $this->setApp($app);
        $this->setDefaults($defaults);
        $this->sendFile();
        exit;
    }

    /**
     * cuurently based on fileforatk\File regarding in which fields cryptic id, filename and path to file are stored.
     */
    public function sendFile(): void
    {
        try {
            $this->checkParameterExistsInRequest();
            $this->loadFile();
            $this->_sendFile();
        } catch (\Throwable $e) {
            http_response_code($e->getCode() >= 400 ?: 500);
        }
    }

    protected function checkParameterExistsInRequest(): void
    {
        if (
            !isset($_GET[$this->paramNameForCryptId])
            || empty($_GET[$this->paramNameForCryptId])
        ) {
            throw new Exception('Required parameter ' . $this->paramNameForCryptId . ' not set in request', 400);
        }
    }

    protected function loadFile(): void
    {
        $this->file = new $this->fileClassName($this->getApp()->db);
        $this->file->loadBy(
            $this->modelIdField,
            $_REQUEST[$this->paramNameForCryptId]
        ); //throws 404 Exception if not found

        $this->getFilePathAndName();
    }

    protected function getFilePathAndName(): void
    {
        $this->filePath = $this->file->getFullFilePath();
        $this->fileName = $this->file->get('value');
    }

    protected function _sendFile(): void
    {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"" . $this->fileName . "\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($this->filePath));

        @readfile($this->filePath);
    }
}