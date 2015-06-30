<?php
namespace Bolt\Tests\Storage\Repository;

use Bolt\Tests\BoltUnitTest;
use Psr\Log\LogLevel;

/**
 * Class to test src/Storage/Repository/LogChangeRepository
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class LogChangeRepositoryTest extends BoltUnitTest
{
    public function testRepositoryQueries()
    {
        $this->resetDb();
        $app = $this->getApp();
        $em = $app['storage'];
        $repo = $em->getRepository('Bolt\Storage\Entity\LogChange');

        $queryTrimLog = $repo->queryTrimLog(7);
        $this->assertEquals('DELETE FROM bolt_log_change WHERE date < :date', $queryTrimLog->getSql());

        $getActivityQueryString = $repo->getActivityQuery(1, 10, LogLevel::INFO, 'foo');
        $this->assertEquals('SELECT * FROM bolt_log_change WHERE (level = :level) AND (context = :context) ORDER BY id DESC LIMIT 10 OFFSET 0', $getActivityQueryString->getSql());

        $getActivityQueryArray = $repo->getActivityQuery(1, 10, LogLevel::ERROR, ['foo', 'bar']);
        $this->assertEquals('SELECT * FROM bolt_log_change WHERE (level = :level) AND ((context = :0) OR (context = :1)) ORDER BY id DESC LIMIT 10 OFFSET 0', $getActivityQueryArray->getSql());

        $getActivityCountQueryString = $repo->getActivityCountQuery(LogLevel::ALERT, 'foo');
        $this->assertEquals('SELECT COUNT(id) as count FROM bolt_log_change WHERE (level = :level) AND (context = :context)', $getActivityCountQueryString->getSql());

        $getActivityCountQueryArray = $repo->getActivityCountQuery(LogLevel::WARNING, ['foo', 'bar']);
        $this->assertEquals('SELECT COUNT(id) as count FROM bolt_log_change WHERE (level = :level) AND ((context = :0) OR (context = :1))', $getActivityCountQueryArray->getSql());
    }
}
