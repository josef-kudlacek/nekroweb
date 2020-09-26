<?php


namespace App\Presenters;

use App\utils\Filter;
use Nette;
use Nette\Application\UI\Form;


class BasePresenter extends Nette\Application\UI\Presenter
{

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

        $this->template->addFilter('semesterType', function ($YearTo) {
            return Filter::semesterType($YearTo);
        });

        $this->template->addFilter('weekDayCZ', function ($number) {
            return Filter::weekDayCZ($number);
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
}