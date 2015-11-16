<?php namespace App\Console\Bootstraper;

use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class LogOutputBootstrapper
 */
class LogOutputBootstrapper
{
    public function bootstrap(Application $app)
    {
        $output = new ConsoleOutput();

        $app->make('log')->listen(function($level, $message) use($output)
        {
            $output->writeln(sprintf("%s <info>[%s]</info> <comment>%s</comment>", date('Y-m-d H:i:s'), $level, $message));
        });
    }
}
