<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\View;

use PMRAtk\App\App;
use PMRAtk\Data\File;
use PMRAtk\tests\phpunit\TestCase;
use PMRAtk\View\FileDownload;
use PMRAtk\View\FileDownloadInline;

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
        $this->app = new App(['nologin'], ['always_run' => false]);
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
        $_REQUEST[$fd->paramNameForCryptID] = 'Duggu';
        $fd->sendFile();
        self::assertEquals('', ob_get_contents());
        ob_end_clean();
        unset($_REQUEST[$fd->paramNameForCryptID]);
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
        $_REQUEST[$fd->paramNameForCryptID] = $file->get('crypt_id');
        @$fd->sendFile();
        self::assertNotFalse(
            strpos(
                ob_get_contents(),
                file_get_contents($file->getFullFilePath())
            )
        );
        ob_end_clean();
        unset($_REQUEST[$fd->paramNameForCryptID]);
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
        $_REQUEST[$fd->paramNameForCryptID] = $file->get('crypt_id');
        @$fd->sendFile();
        self::assertNotFalse(
            strpos(
                ob_get_contents(),
                file_get_contents($file->getFullFilePath())
            )
        );
        ob_end_clean();
        unset($_REQUEST[$fd->paramNameForCryptID]);
    }
}