<?php

use Mockery as m;

class EmailTemplateTest extends PHPUnit_Framework_TestCase
{
    use Laravel;

    public function setUp()
    {
        $this->migrate('up');
    }

    public function tearDown()
    {
        m::close();
        $this->migrate('down');
        $this->destroyApplication();
    }

    public function test_mailer()
    {
        $app = $this->createApplication();

        $slug = 'test';

        $data = [
            'slug'         => 'test',
            'subject'      => 'test',
            'from_address' => 'test',
            'from_name'    => 'test',
            'content'      => 'test',
        ];

        Recca0120\EmailTemplate\EmailTemplate::create($data);

        $mailer = m::mock('Illuminate\Contracts\Mail\Mailer')
            ->shouldReceive('alwaysFrom')
            ->shouldReceive('send')
            ->mock();

        $filesystem = m::mock('Illuminate\Filesystem\Filesystem')
            ->makePartial()
            ->shouldReceive('isDirectory')->twice()->andReturn(false)
            ->shouldReceive('makeDirectory')->twice()->andReturn(true)
            ->shouldReceive('exists')->andReturn(true)
            ->shouldReceive('lastModified')->andReturn(time() + 1000)
            ->mock();

        $viewFactory = m::mock('Illuminate\Contracts\View\Factory')
            ->shouldReceive('addNamespace')
            ->mock();

        $mailer = new \Recca0120\EmailTemplate\Mailer($mailer, $filesystem, $viewFactory);
        $mailer->send($slug, [], function () {
        });
        $model = $mailer->getAttributes($slug);
        $this->assertEquals(array_except($model->toArray(), ['id', 'created_at', 'updated_at']), $data);
    }
}
