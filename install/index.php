<?

use Bitrix\Main\ModuleManager;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\IO\Directory;

class wc_sale extends CModule
{
    var $MODULE_ID = 'wc.sale';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    private $kernelDir;

    public function __construct()
    {
        Loc::loadMessages(__FILE__);
        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }

        $this->MODULE_NAME = Loc::getMessage('WC_SALE_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('WC_SALE_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('WC_SALE_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('WC_SALE_PARTNER_URI');

        $kernelDir = Directory::isDirectoryExists($_SERVER['DOCUMENT_ROOT'] . '/local') ? '/local' : '/bitrix';
        $this->kernelDir = $_SERVER['DOCUMENT_ROOT'] . $kernelDir;
    }

    function DoInstall()
    {
        global $APPLICATION;
        $result = true;

        try {
            $this->checkRequirements();
            Main\ModuleManager::registerModule($this->MODULE_ID);
            if (Main\Loader::includeModule($this->MODULE_ID)) {
                $this->InstallEvents();
                $this->InstallFiles();
            } else {
                throw new Main\SystemException(Loc::getMessage('WC_MAIN_MODULE_NOT_REGISTERED'));
            }
        } catch (Main\SystemException $exception) {
            $result = false;
            $APPLICATION->ThrowException($exception->getMessage());
        }

        return $result;
    }

    function DoUninstall()
    {
        if (Main\Loader::includeModule($this->MODULE_ID)) {
            $this->UnInstallEvents();
            $this->UnInstallFiles();
        }

        Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    function InstallEvents()
    {
        // todo \WC\IBlock\UniqueSymbolCode
    }

    function UnInstallEvents()
    {
        // todo \WC\IBlock\UniqueSymbolCode
    }

    function InstallFiles()
    {
        CopyDirFiles(__DIR__ . '/components', $this->kernelDir . "/components", true, true);
        CopyDirFiles(__DIR__ . '/js', $this->kernelDir . "/js", true, true);
    }

    function UnInstallFiles()
    {
        //Directory::deleteDirectory($this->kernelDir . '/components/wc/order');
    }

    private function checkRequirements()
    {
        $requirePhp = '7.1';
        if (CheckVersion(PHP_VERSION, $requirePhp) === false) {
            throw new Main\SystemException(Loc::getMessage('WC_SALE_INSTALL_REQUIRE_PHP', ['#VERSION#' => $requirePhp]));
        }

        $requireModules = [
            'main' => '17.5.0',
            'iblock' => '15.0.0',
            'sale' => '15.0.0',
            'wc.core' => '0.0.1',
        ];

        if (class_exists(ModuleManager::class)) {
            foreach ($requireModules as $moduleName => $moduleVersion) {
                $currentVersion = Main\ModuleManager::getVersion($moduleName);
                if (CheckVersion($currentVersion, $moduleVersion) === false) {
                    throw new Main\SystemException(Loc::getMessage('WC_SALE_INSTALL_REQUIRE_MODULE', [
                        '#MODULE#' => $moduleName,
                        '#VERSION#' => $moduleVersion,
                    ]));
                }
            }
        }
    }
}
