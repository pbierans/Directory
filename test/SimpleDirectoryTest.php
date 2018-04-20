<?php /** @noinspection PhpUndefinedFieldInspection */

namespace PatrickBierans\Directory;

use PHPUnit\Framework\TestCase;

class SimpleDirectoryTest extends TestCase {

    public function test(): void {
        SimpleDirectory::$autoCreateMetaFiles = false;

        $d = new SimpleDirectory(__DIR__ . '/dir');
        $this->assertEquals('title of directory', $d->title);
        $this->assertEquals('description of directory', $d->description);

        $this->assertEquals(12, $d->count(), 'we do not count directories or files which are forbidden (starting with _ or .)');
        $this->assertCount(12, $d, 'checking countable interface');

        $iteration = 0;
        foreach ($d as $info) {
            $this->assertInstanceOf(\SplFileInfo::class, $info->fileinfo, 'we have some SplFileInfo here');
            $iteration++;
        }

        $this->assertEquals(\count($d), $iteration, 'checking countable interface');

    }

}
