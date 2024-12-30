<?php

namespace App\Console;

use App\Scrubbers\ScrabberInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

#[AsCommand('scrub', 'Add new house to comparator')]
class ScrubUrl extends Command
{
    public function __construct(
        private string $dataDir,
        /** @var ScrabberInterface[] */
        #[AutowireIterator(tag: ScrabberInterface::TAG)]
        private iterable $scrubbers
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('url', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = $input->getArgument('url');

        foreach ($this->scrubbers as $scrubber) {
            if ($scrubber->supportsUrl($url)) {
                $house = $scrubber->scrab($url);
                break;
            }
        }

        if (!isset($house)) throw new \RuntimeException(sprintf('Unsupported link "%s"', $url));

        var_dump($house);

        $filename = $this->dataDir.'catalog.bin';
        $catalog = file_exists($filename) ? unserialize(file_get_contents($filename)) : [];
        $catalog[] = $house;

        file_put_contents($filename, serialize($catalog), LOCK_EX);

        return self::SUCCESS;
    }
}
