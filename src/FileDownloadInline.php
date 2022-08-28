<?php declare(strict_types=1);

namespace atkuifiledownload;

class FileDownloadInline extends FileDownload
{

    protected function _sendFile()
    {
        header('Content-Type: ' . mime_content_type($this->currentFilePath));
        header('Content-Length: ' . filesize($this->currentFilePath));
        header('Content-Disposition: inline; filename="' . $this->currentFileName . '"');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: public');
        header('Expires: 0');

        @readfile($this->currentFilePath);
    }
}