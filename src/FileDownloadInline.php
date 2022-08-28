<?php declare(strict_types=1);

namespace atkuifiledownload;

class FileDownloadInline extends FileDownload
{

    protected function _sendFile(): void
    {
        header('Content-Type: ' . mime_content_type($this->filePath));
        header('Content-Length: ' . filesize($this->filePath));
        header('Content-Disposition: inline; filename="' . $this->fileName . '"');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: public');
        header('Expires: 0');

        @readfile($this->filePath);
    }
}