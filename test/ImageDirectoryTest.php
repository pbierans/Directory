<?php /** @noinspection PhpUndefinedFieldInspection */

namespace PatrickBierans\Directory;

use PHPUnit\Framework\TestCase;

class ImageDirectoryTest extends TestCase {

    public function test(): void {
        ImageDirectory::$autoCreateMetaFiles = false;

        $d = new ImageDirectory(__DIR__ . '/dir');
        $this->assertEquals('title of directory', $d->title);
        $this->assertEquals('description of directory', $d->description);

        $this->assertEquals(3, $d->count(), 'we do not count directories at all. nor files which are forbidden (starting with _ or .). nor files of wrong filetype');
        $this->assertCount(3, $d, 'checking countable interface');

        $expected = $this->expectedData();
        $iteration = 0;
        foreach ($d as $info) {
            $this->assertEquals($expected[$iteration][0], $info->basename, 'basename matching');
            $this->assertEquals($expected[$iteration][1], $info->title, 'title matching');
            $this->assertEquals($expected[$iteration][2], $info->description, 'description matching');
            $this->assertEquals($expected[$iteration][3], $info->comment, 'comment matching');
            $iteration++;
        }

        $this->assertEquals(\count($d), $iteration, 'checking countable interface');
    }

    public function expectedData(): array {
        return [
            // basename, title, description, comment
            ['a.jpg', 'title of image a', 'description of image a', null],
            ['b.png', null, null, 'comment of image b with newlines and spaces to be trimmed'],
            ['c.gif', null, null, null],
        ];
    }

}
