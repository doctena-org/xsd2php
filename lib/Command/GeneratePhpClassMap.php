<?php
namespace Goetas\Xsd\XsdToPhp\Command;

use Goetas\Xsd\XsdToPhp\Php\PhpConverter;
use Goetas\Xsd\XsdToPhp\Php\ClassGenerator;
use Goetas\Xsd\XsdToPhp\Php\PathGenerator\Psr4PathGenerator;
use Goetas\Xsd\XsdToPhp\AbstractConverter;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Code\Generator\FileGenerator;
use Goetas\Xsd\XsdToPhp\Naming\NamingStrategy;

class GeneratePhpClassMap extends AbstractConvert
{
    /**
     *
     * @see Console\Command\Command
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('generate:php:classmap');
        $this->setDescription('Convert XSD definitions into a PHP class map');
    }

    protected function getConverterter(NamingStrategy $naming)
    {
        return new PhpConverter($naming);
    }

    protected function convert(AbstractConverter $converter, array $schemas, array $targets, OutputInterface $output)
    {
        $progress = $this->getHelperSet()->get('progress');

        $items = $converter->convert($schemas);
        $progress->start($output, count($items));

        $clazzMapString = "<?php \n\nreturn ";
        $clazzMap = array();

        foreach ($items as $item) {
            $progress->advance(1, true);
            $clazz = $output->getFormatter()->escape($item->getFullName());
            $output->write(" Processing <info>" . $clazz . "</info>... ");
            $clazzName = array_pop(explode('\\', $clazz));
            $clazzMap[$clazzName] = $clazz;
        }

        $clazzMapString .= var_export($clazzMap, true) .';';

        $output->writeln("Writing the class map.");

        $outputFile = $this->input->getOption('output-file');

        if (!empty($outputFile)) {
            file_put_contents($outputFile, $clazzMapString);
        } else {
            $output->writeln("You didn't specify an output file, writing to stdout.");
            $output->writeln($clazzMapString);
        }

        $progress->finish();
    }
}
