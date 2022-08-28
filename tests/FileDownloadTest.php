<?php declare(strict_types=1);

namespace atkuifiledownload\tests;


use Atk4\Ui\App;
use atkuifiledownload\FileDownload;
use atkuifiledownload\FileDownloadInline;
use fileforatk\File;
use traitsforatkdata\TestCase;

class FileDownloadTest extends TestCase
{

    private $app;
    private $persistence;

    protected $sqlitePersistenceModels = [
        File::class
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->app = new App(['always_run' => false]);
        $this->persistence = $this->getSqliteTestPersistence();
        $this->app->db = $this->persistence;
        $this->persistence->app = $this->app;
    }

    public function testExitOnNoId()
    {
        ob_start();
        $fd = new FileDownload($this->app);
        $fd->sendFile();
        self::assertEquals('', ob_get_contents());
        ob_end_clean();
    }

    public function testExitOnFileNotFound()
    {
        ob_start();
        $fd = new FileDownload($this->app);
        $_REQUEST[$fd->paramNameForCryptId] = 'Duggu';
        $fd->sendFile();
        self::assertEquals('', ob_get_contents());
        ob_end_clean();
        unset($_REQUEST[$fd->paramNameForCryptId]);
        self::assertEquals(http_response_code(), 404);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendFileByCryptId()
    {
        $file = new File($this->persistence);
        $file->set('value', 'demo_file.txt');
        $file->set('path', 'tests/');
        $file->save();

        ob_start();
        $fd = new FileDownLoad($this->app);
        $_REQUEST[$fd->paramNameForCryptId] = $file->get('crypt_id');
        @$fd->sendFile();
        self::assertNotFalse(
            strpos(
                ob_get_contents(),
                file_get_contents($file->getFullFilePath())
            )
        );
        ob_end_clean();
        unset($_REQUEST[$fd->paramNameForCryptId]);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSendInlineFileByCryptId()
    {
        $file = new File($this->persistence);
        $file->set('value', 'demo_file.txt');
        $file->set('path', 'tests/');
        $file->save();

        ob_start();
        $fd = new FileDownloadInline($this->app);
        $_REQUEST[$fd->paramNameForCryptId] = $file->get('crypt_id');
        @$fd->sendFile();
        self::assertNotFalse(
            strpos(
                ob_get_contents(),
                file_get_contents($file->getFullFilePath())
            )
        );
        ob_end_clean();
        unset($_REQUEST[$fd->paramNameForCryptId]);
    }
}