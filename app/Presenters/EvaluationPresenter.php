<?php


namespace App\Presenters;

use App\Model;
use App\Utils\Utils;
use Nette\Application\UI\Form;

class EvaluationPresenter extends BasePresenter
{
    private $evaluation;

    public function __construct(Model\Evaluation $evaluation)
    {
        $this->evaluation = $evaluation;
    }

    public function renderShow()
    {
        $this->template->evaluationStats = $this->evaluation->getEvaluationStats()->fetch();
        $this->template->evaluations = $this->evaluation->getEvaluations();
    }

}