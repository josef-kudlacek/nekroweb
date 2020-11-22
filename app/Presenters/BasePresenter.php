<?php


namespace App\Presenters;

use App\Model;
use App\Utils\Filter;
use App\Utils\Functions;
use Nette;
use Nette\Application\UI\Form;


class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var Model\Quote
     * @inject
     */
    public $quote;


    protected function startup()
    {
        parent::startup();
    }

    protected function beforeRender()
    {
        $this->loadTemplateFilters();
        $this->loadTemplateFunctions();

        $quotes = $this->quote->getQuotes()->fetchAll();
        $this->template->quote = $this->generateQuote($quotes);
    }

    private function loadTemplateFilters()
    {
        $this->template->addFilter('assessmentWeight', function ($assessmentWeight) {
            return Filter::assessmentWeight($assessmentWeight);
        });

        $this->template->addFilter('attendanceType', function ($attendance) {
            return Filter::attendanceType($attendance);
        });

        $this->template->addFilter('houseType', function ($houseId) {
            return Filter::houseType($houseId);
        });

        $this->template->addFilter('markColor', function ($mark) {
            return Filter::markColor($mark);
        });

        $this->template->addFilter('markType', function ($markId) {
            return Filter::markType($markId);
        });

        $this->template->addFilter('semesterType', function ($YearTo) {
            return Filter::semesterType($YearTo);
        });

        $this->template->addFilter('competitionNoStyle', function ($competitionNumber) {
            return Filter::competitionNoStyle($competitionNumber);
        });

        $this->template->addFilter('weekDayCZ', function ($number) {
            return Filter::weekDayCZ($number);
        });

        $this->template->addFilter('encodeToWin1250', function ($stringUrl) {
            return Filter::encodeToCharset($stringUrl, "windows-1250");
        });
    }

    private function loadTemplateFunctions()
    {
        $this->template->addFunction('countPercentStars', function ($starsCount, $starsSum) {
            return Functions::calculatePercentStars($starsCount, $starsSum);
        });
    }

    public function errorForm(Form $form){
        if ($form->getErrors()){
            foreach ($form->getErrors() as $value)
            {
                $this->flashMessage($value,'danger');
            }
        }
    }

    private function generateQuote($quotes)
    {
        $quotesCount = count($quotes);
        $number = rand(0, $quotesCount * 3);

        if ($number > $quotesCount - 1)
        {
            return null;
        }

        return $quotes[$number];
    }
}