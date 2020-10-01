<?php


namespace App\Presenters;

use App\Utils\Filter;
use Nette;
use Nette\Application\UI\Form;


class BasePresenter extends Nette\Application\UI\Presenter
{

    protected function startup()
    {
        parent::startup();
    }

    protected function beforeRender()
    {
        $this->loadTemplateFilters();
    }

    private function loadTemplateFilters()
    {

        $this->template->addFilter('attendanceType', function ($attendance) {
            return Filter::attendanceType($attendance);
        });

        $this->template->addFilter('houseType', function ($houseId) {
            return Filter::houseType($houseId);
        });

        $this->template->addFilter('markType', function ($markId) {
            return Filter::markType($markId);
        });

        $this->template->addFilter('semesterType', function ($YearTo) {
            return Filter::semesterType($YearTo);
        });

        $this->template->addFilter('weekDayCZ', function ($number) {
            return Filter::weekDayCZ($number);
        });

        $this->template->addFilter('encodeToWin1250', function ($stringUrl) {
            return Filter::encodeToCharset($stringUrl, "windows-1250");
        });
    }

    public function errorForm(Form $form){
        if($form->getErrors()){
            foreach ($form->getErrors() as $value)
            {
                $this->flashMessage($value,'danger');
            }
        }
    }

    private function checkAccess()
    {
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.','danger');
            $this->redirect('Homepage:default');
        }
    }
}