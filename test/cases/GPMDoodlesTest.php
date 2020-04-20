<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error\Deprecated;
use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\Error\Notice;
use SepiaRiver\GPMDoodles;

class GPMDoodlesTest extends TestCase
{
    protected $projectPath;
    protected $modx;
    protected $gpmdoodles; // gateway class
    
    protected function setUp(): void
    {
        # Deprecated:
        Deprecated::$enabled = FALSE;

        $this->projectPath = dirname(dirname(dirname(__FILE__)));

        require_once($this->projectPath . '/config.core.php');
        require_once(MODX_CORE_PATH . 'model/modx/modx.class.php');
        $this->modx = new modX();
        $this->modx->initialize('web');

        $corePath = $this->modx->getOption('gpmdoodles.core_path', null, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/gpmdoodles/');
        /** @var GPMDoodles $gpmdoodles */
        $this->gpmdoodles = $this->modx->getService('gpmdoodles', 'GPMDoodles', $corePath . 'model/gpmdoodles/', ['core_path' => $corePath]);
    }

    public function testInstantiation()
    {
        $this->assertTrue($this->modx instanceof modX);
        $this->assertTrue($this->modx->context instanceof modContext);
        $this->assertEquals('web', $this->modx->context->key);
        $this->assertTrue($this->gpmdoodles instanceof GPMDoodles);
    }
}
