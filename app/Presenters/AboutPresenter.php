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
        $this->template->history = $this->isLogged();
    }

    private function isLogged()
    {
        parent::startup();
        if ($this->getUser()->loggedIn) {
            $userIdentity = $this->getUser()->getIdentity();

            return $this->about->getHistoryBySemester($userIdentity->semesterFrom, $userIdentity->semesterTo);
        } else {
            return $this->about->getHistory();
        }
    }
}