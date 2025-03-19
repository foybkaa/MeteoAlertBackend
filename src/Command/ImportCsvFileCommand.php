<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'import-csv-file',
    description: 'Importe une liste de destinataires depuis un fichier CSV',
)]
class ImportCsvFileCommand extends Command
{
    private Connection $connection;
    private LoggerInterface $logger;

    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        parent::__construct();
        $this->connection = $connection;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Chemin du fichier CSV à importer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');

        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->logger->error('Le fichier spécifié est introuvable ou illisible.', ['file' => $filePath]);
            $io->error('Le fichier spécifié est introuvable ou illisible.');
            return Command::FAILURE;
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->logger->error('Impossible d\'ouvrir le fichier.', ['file' => $filePath]);
            $io->error('Impossible d\'ouvrir le fichier.');
            return Command::FAILURE;
        }

        // Lire l'en-tête
        $headers = fgetcsv($handle, 1000, ',', '"', '\\');
        if (!$headers || !in_array('insee', $headers) || !in_array('telephone', $headers)) {
            $this->logger->error('Le fichier CSV doit contenir les colonnes "insee" et "telephone".');
            $io->error('Le fichier CSV doit contenir les colonnes "insee" et "telephone".');
            fclose($handle);
            return Command::FAILURE;
        }

        $successCount = 0;
        $errorCount = 0;

        while (($row = fgetcsv($handle, 1000, ',', '"', '\\')) !== false) {
            $data = array_combine($headers, $row);
            $insee = $data['insee'];
            $telephone = $data['telephone'];

            if ($this->isValidInsee($insee) && $this->isValidPhoneNumber($telephone)) {
                try {
                    $this->connection->insert('destinataires', [
                        'insee' => $insee,
                        'telephone' => $telephone
                    ]);
                    $this->logger->info('Destinataire importé avec succès', ['insee' => $insee, 'telephone' => $telephone]);
                    $successCount++;
                } catch (\Exception $e) {
                    $this->logger->error('Erreur lors de l\'importation du destinataire', ['insee' => $insee, 'telephone' => $telephone, 'error' => $e->getMessage()]);
                    $errorCount++;
                }
            } else {
                $this->logger->warning('Données invalides pour le destinataire', ['insee' => $insee, 'telephone' => $telephone]);
                $errorCount++;
            }
        }

        fclose($handle);

        $this->logger->info('Import terminé', ['success' => $successCount, 'errors' => $errorCount]);
        $io->success("Import terminé : $successCount succès, $errorCount erreurs.");
        return Command::SUCCESS;
    }
    private function isValidInsee(?string $insee): bool
    {
        return $insee && preg_match('/^\d{5}$/', $insee);
    }

    private function isValidPhoneNumber(?string $phone): bool
    {
        return $phone && preg_match('/^(\+33|0)[67]\d{8}$/', $phone);
    }
}
