<?php

namespace Recca0120\EmailTemplate;

use Closure;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Factory as ViewFactory;

class Mailer
{
    protected $namespace = 'email-template';
    protected $model;
    protected $mailer;
    protected $filesystem;
    protected $viewFactory;
    protected $cache;

    public function __construct(EmailTemplate $model,
                            MailerContract $mailer,
                            Filesystem $filesystem,
                            ViewFactory $viewFactory)
    {
        $this->model = $model;
        $this->filesystem = $filesystem;
        $this->mailer = $mailer;
        $this->viewFactory = $viewFactory;

        $this->viewFactory->addNamespace($this->namespace, $this->storagePath());
    }

    public function storagePath()
    {
        $path = storage_path('email-template');
        if ($this->filesystem->isDirectory($path) === false) {
            $this->filesystem->makeDirectory($path, 0755, true);
        }

        return $path.'/';
    }

    public function getAttributes($slug)
    {
        if (empty($this->cache[$slug]) === true) {
            $this->cache[$slug] = $this->model
                ->where('slug', '=', $slug)
                ->first();
        }

        return $this->cache[$slug];
    }

    public function getView($slug)
    {
        $attributes = $this->getAttributes($slug);
        $file = $this->storagePath().md5($slug).'.blade.php';
        if ($this->filesystem->exists($file) === false || $attributes->updated_at->getTimestamp() > $this->filesystem->lastModified($file)) {
            $this->filesystem->put($file, $attributes->content);
        }

        return $this->namespace.'::'.md5($slug);
    }

    public function send($slug, $data = [], Closure $closure)
    {
        $view = $this->getView($slug);
        $attributes = $this->getAttributes($slug);

        if (empty($attributes->from_address) === false) {
            $this->mailer->alwaysFrom($attributes->from_address, $attributes->from_name);
        }

        $sended = $this->mailer->send($slug, $data, $closure);

        return $sended;
    }

    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->mailer, $method], $parameters);
    }
}
