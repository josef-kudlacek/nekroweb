<?php


namespace App\Presenters;

use App\Model;
use App\utils\Utils;
use Nette\Application\UI\Form;

class ActivityPresenter
{
    private $activity;

    public function __construct(Model\Activity $activity)
    {
        $this->activity = $activity;
    }

    protected function startup()
    {
        parent::startup();
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.','danger');
            $this->redirect('Homepage:default');
        }
    }



}