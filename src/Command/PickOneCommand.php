<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use App\Exception\InvalidArgumentException;
use App\Model\Entity\User;
use App\Model\Table\UsersTable;

/**
 * Class PickOneCommand
 * A simple command that displays hello world to the console.
 *
 * @package App\Command
 */
class PickOneCommand extends Command
{
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('pick-one');
        $this->addArgument(
            'filename',
            InputArgument::REQUIRED,
            'The JSON file from where to read the users'
        );

        $this->addOption(
            'country-code',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Country from where to pick the user',
            ''
        );
    }

    /**
     * Execute the command from the console.
     *
     * @param InputInterface $input the input interface
     * @param OutputInterface $output the output interface
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');
        $country_code = strtoupper($input->getOption('country-code'));

        UsersTable::instance()
            ->setFilename($filename);

        $users = UsersTable::instance()->listAll();

        if ($country_code !== '') {
            $users = self::filterByCountry($users, $country_code);

            $numUsers = sizeof($users);
            $message = "There are $numUsers users from `$country_code`.";
        } else {
            $numUsers = sizeof($users);
            $message = "There are $numUsers users.";
        }

        $output->writeln($message);

        if ($numUsers === 0) {
            exit(0);
        }

        $output->writeln("\nRandomizing a user...\n");

        $len = $numUsers;
        $selected = $users[rand(0, $len)];
        $output->write(
            json_encode(User::toArray($selected), JSON_PRETTY_PRINT) . "\n\n"
        );
    }

    private static function filterByCountry($users, $country)
    {
        $filtered_users = [];
        foreach ($users as $user) {
            if ($user->country === $country) {
                $filtered_users[] = $user;
            }
        }

        return $filtered_users;
    }
}
