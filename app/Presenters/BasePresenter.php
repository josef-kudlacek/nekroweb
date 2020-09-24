<?php


namespace App\Presenters;

use App\utils\Filter;
use Nette;


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
}