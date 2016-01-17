<?php

namespace Recca0120\EmailTemplate;

use Closure;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Filesystem\Filesystem;

class Mailer
{
    /**
     * view namespace.
     *
     * @var string
     */
    protected $viewNamespace = 'email-template';

    /**
     * mailer.
     *
     * @var \Illuminate\Contracts\Mail\Mailer
     */
    protected $mailer;

    /**
     * Filesystem.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * view factory.
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $viewFactory;

    /**
     * view cache.
     *
     * @var array
     */
    protected $viewCache = [];

    /**
     * construct.
     *
     * @param \Illuminate\Contracts\Mail\Mailer $mailer
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     * @param \Illuminate\Contracts\View\Factory $viewFactory
     */
    public function __construct(
        MailerContract $mailer,
        Filesystem $filesystem,
        ViewFactory $viewFactory
    ) {
        $this->filesystem = $filesystem;
        $this->mailer = $mailer;
        $this->viewFactory = $viewFactory;
        $this->viewFactory->addNamespace($this->viewNamespace, $this->storagePath());
    }

    /**
     * storage path.
     *
     * @return string
     */
    public function storagePath()
    {
        $path = storage_path('email-template');
        if ($this->filesystem->isDirectory($path) === false) {
            $this->filesystem->makeDirectory($path, 0755, true);
        }

        return $path.'/';
    }

    /**
     * attributes.
     *
     * @param  string $slug [description]
     * @return \Recca0120\EmailTemplate\EmailTemplate
     */
    public function getAttributes($slug)
    {
        if (empty($this->viewCache[$slug]) === true) {
            $this->viewCache[$slug] = EmailTemplate::$twhere('slug', '=', $slug)
                ->first();
        }

        return $this->viewCache[$slug];
    }

    /**
     * get view.
     *
     * @param  string $slug
     * @return string
     */
    public function getView($slug)
    {
        $attributes = $this->getAttributes($slug);
        $file = $this->storagePath().md5($slug).'.blade.php';
        if ($this->filesystem->exists($file) === false ||
            $attributes->updated_at->getTimestamp() > $this->filesystem->lastModified($file)
        ) {
            $this->filesystem->put($file, $attributes->content);
        }

        return $this->viewNamespace.'::'.md5($slug);
    }

    /**
     * send.
     *
     * @param  string $slug
     * @param  array $data
     * @param  Closure $closure
     * @return bool
     */
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

    /**
     * _call.
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->mailer, $method], $parameters);
    }
}
