<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     performance_tests
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Performance_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Performance_Config
     */
    protected $_object;

    /**
     * @var string
     */
    protected $_fixtureDir;

    /**
     * @var array
     */
    protected $_fixtureConfigData;

    protected function setUp()
    {
        $this->_fixtureDir = __DIR__ . DIRECTORY_SEPARATOR . '_files';
        $this->_fixtureConfigData = require $this->_fixtureDir . '/config_data.php';
        $this->_object = new Magento_Performance_Config(
            $this->_fixtureConfigData, $this->_fixtureDir, $this->_getFixtureAppBaseDir()
        );
    }

    protected function tearDown()
    {
        unset($this->_object);
    }

    /**
     * @dataProvider constructorExceptionDataProvider
     * @param array $configData
     * @param string $baseDir
     * @param string $expectedException
     * @param string $expectedExceptionMsg
     */
    public function testConstructorException(array $configData, $baseDir, $expectedException, $expectedExceptionMsg)
    {
        $this->setExpectedException($expectedException, $expectedExceptionMsg);
        new Magento_Performance_Config($configData, $baseDir, $this->_getFixtureAppBaseDir());
    }

    /**
     * Get simulated application base directory
     *
     * @return string
     */
    protected function _getFixtureAppBaseDir()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'app_base_dir';
    }

    /**
     * @return array
     */
    public function constructorExceptionDataProvider()
    {
        return array(
            'non-existing base dir' => array(
                require __DIR__ . '/_files/config_data.php',
                'non_existing_dir',
                'Magento_Exception',
                "Base directory 'non_existing_dir' does not exist",
            ),
            'invalid scenarios format' => array(
                require __DIR__ . '/_files/config_data_invalid_scenarios_format.php',
                __DIR__ . DIRECTORY_SEPARATOR . '_files',
                'InvalidArgumentException',
                "'scenario' => 'scenarios' option must be an array",
            ),
            'no scenario title' => array(
                require __DIR__ . '/_files/config_no_title.php',
                __DIR__ . DIRECTORY_SEPARATOR . '_files',
                'InvalidArgumentException',
                'Scenario must have a title',
            ),
            'bad users scenario argument' => array(
                require __DIR__ . '/_files/config_bad_users.php',
                __DIR__ . DIRECTORY_SEPARATOR . '_files',
                'InvalidArgumentException',
                "Scenario 'Scenario' must have a positive integer argument 'users'.",
            ),
            'bad loops scenario argument' => array(
                require __DIR__ . '/_files/config_bad_loops.php',
                __DIR__ . DIRECTORY_SEPARATOR . '_files',
                'InvalidArgumentException',
                "Scenario 'Scenario' must have a positive integer argument 'loops'.",
            ),
            'invalid scenario fixtures format' => array(
                require __DIR__ . '/_files/config_invalid_fixtures_format.php',
                __DIR__ . DIRECTORY_SEPARATOR . '_files',
                'InvalidArgumentException',
                "'fixtures' for scenario 'Scenario' must be represented by an array",
            ),
            'no scenario file defined' => array(
                require __DIR__ . '/_files/config_no_file_defined.php',
                __DIR__ . DIRECTORY_SEPARATOR . '_files',
                'InvalidArgumentException',
                "File is not defined for scenario 'Scenario'",
            ),
            'non-existing scenario file' => array(
                require __DIR__ . '/_files/config_non_existing_file.php',
                __DIR__ . DIRECTORY_SEPARATOR . '_files',
                'InvalidArgumentException',
                "File non_existing_file.jmx doesn't exist for scenario 'Scenario'",
            ),
            'non-existing scenario fixture' => array(
                require __DIR__ . '/_files/config_non_existing_fixture.php',
                __DIR__ . DIRECTORY_SEPARATOR . '_files',
                'InvalidArgumentException',
                "Fixture 'non_existing_fixture.php' doesn't exist",
            ),
        );
    }

    public function testGetApplicationBaseDir()
    {
        $this->assertEquals($this->_getFixtureAppBaseDir(), $this->_object->getApplicationBaseDir());
    }

    public function testGetApplicationUrlHost()
    {
        $this->assertEquals('127.0.0.1', $this->_object->getApplicationUrlHost());
    }

    public function testGetApplicationUrlPath()
    {
        $this->assertEquals('/', $this->_object->getApplicationUrlPath());
    }

    public function testGetAdminOptions()
    {
        $expectedOptions = array(
            'frontname' => 'backend',
            'username' => 'admin',
            'password' => 'password1',
        );
        $this->assertEquals($expectedOptions, $this->_object->getAdminOptions());
    }

    public function testGetInstallOptions()
    {
        $expectedOptions = array('option1' => 'value 1', 'option2' => 'value 2');
        $this->assertEquals($expectedOptions, $this->_object->getInstallOptions());
    }

    public function testGetScenarios()
    {
        $actualScenarios = $this->_object->getScenarios();

        // Assert array of scenarios is correctly composed
        $this->assertInternalType('array', $actualScenarios);
        $this->assertCount(3, $actualScenarios);

        // Assert that the data is passed to scenarios successfully
        /** @var $scenario Magento_Performance_Scenario */
        $scenario = $actualScenarios[0];
        $this->assertInstanceOf('Magento_Performance_Scenario', $scenario);

        $this->assertEquals('Scenario', $scenario->getTitle());
        $this->assertEquals(realpath(__DIR__ . '/_files/scenario.jmx'), $scenario->getFile());

        // Assert that default config is applied
        $expectedArguments = array(
            Magento_Performance_Scenario::ARG_USERS             => 1,
            Magento_Performance_Scenario::ARG_LOOPS             => 1,
            Magento_Performance_Scenario::ARG_HOST              => '127.0.0.1',
            Magento_Performance_Scenario::ARG_PATH              => '/',
            Magento_Performance_Scenario::ARG_ADMIN_FRONTNAME   => 'backend',
            Magento_Performance_Scenario::ARG_ADMIN_USERNAME    => 'admin',
            Magento_Performance_Scenario::ARG_ADMIN_PASSWORD    => 'password1',
            Magento_Performance_Scenario::ARG_BASEDIR           => $this->_getFixtureAppBaseDir(),
            'arg1'                                              => 'value 1',
            'arg2'                                              => 'overridden value 2',
            'arg3'                                              => 'custom value 3'
        );
        $this->assertEquals($expectedArguments, $scenario->getArguments());

        $expectedSettings = array(
            'setting1' => 'setting 1',
            'setting2' => 'overridden setting 2',
            'setting3' => 'setting 3'
        );
        $this->assertEquals($expectedSettings, $scenario->getSettings());

        $expectedSettings = array(
            'setting1' => 'setting 1',
            'setting2' => 'overridden setting 2',
            'setting3' => 'setting 3'
        );
        $this->assertEquals($expectedSettings, $scenario->getSettings());

        $expectedFixtures = array(
            realpath(__DIR__ . '/_files/fixture.php'),
            realpath(__DIR__ . '/_files/fixture2.php'),
        );
        $this->assertEquals($expectedFixtures, $scenario->getFixtures());
    }

    public function testGetReportDir()
    {
        $expectedReportDir = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'report';
        $this->assertEquals($expectedReportDir, $this->_object->getReportDir());
    }
}
