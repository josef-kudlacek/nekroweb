<?php


namespace App\Presenters;

use App\Model;


class AboutPresenter extends BasePresenter
{
    private $about;

    public function __construct(Model\About $about)
    {
        $this->about = $about;
    }

    public function renderShow()
    {
        $this->template->history = $this->about->getHistory();
    }
}