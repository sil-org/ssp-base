<?php

include __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use SimpleSAML\Configuration;
use SimpleSAML\Module;
use SimpleSAML\XHTML\Template;
use Symfony\Bridge\Twig\Command\LintCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class TwigTemplatesTest extends TestCase
{

    /**
     * Ensure the material theme's Twig templates have valid syntax. SimpleSAMLphp's Twig environment is used so its
     * custom filters (e.g. "trans") are recognized; any existing template name can be used to create it.
     */
    public function testTemplateSyntax()
    {
        $twig = (new Template(Configuration::getInstance(), 'core:welcome'))->getTwig();

        $lint = new CommandTester(new LintCommand($twig));
        $exitCode = $lint->execute(['filename' => [Module::getModuleDir('material') . '/themes']]);

        $this->assertSame(Command::SUCCESS, $exitCode, $lint->getDisplay());
    }

}
