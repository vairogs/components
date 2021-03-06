<?php declare(strict_types = 1);

namespace Vairogs\Component\Utils\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Monolog\Handler\AbstractProcessingHandler;
use Vairogs\Component\Utils\Doctrine\Model\Log;

class MonologDBHandler extends AbstractProcessingHandler
{
    /**
     * @param EntityManagerInterface|ObjectManager $em
     * @param ManagerRegistry $managerRegistry
     * @param string $logClass
     */
    public function __construct(protected EntityManagerInterface|ObjectManager $em, protected ManagerRegistry $managerRegistry, protected string $logClass)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records): void
    {
        $this->em->beginTransaction();
        foreach ($records as $record) {
            $this->handle($record);
        }
        $this->em->commit();
    }

    /**
     * @param array $record
     */
    protected function write(array $record): void
    {
        /** @var Log $entry */
        $entry = new $this->logClass();
        $entry->setMessage($record['message']);
        $entry->setLevel($record['level']);
        $entry->setLevelName($record['level_name']);
        $entry->setExtra($record['extra']);
        $entry->setContext($record['context']);

        if (!$this->em->isOpen()) {
            $this->em = $this->managerRegistry->resetManager();
        }

        $this->em->persist($entry);
        $this->em->flush();
    }
}
