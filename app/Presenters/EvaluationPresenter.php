<?php


namespace App\Presenters;

use App\Model;
use App\Utils\Utils;
use Nette\Application\UI\Form;

class EvaluationPresenter extends BasePresenter
{
    private $evaluation;

    /** @var Model\Attendance
     * @inject
     */
    public $attendance;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;

    public function __construct(Model\Evaluation $evaluation)
    {
        $this->evaluation = $evaluation;
    }

    public function renderShow()
    {
        $this->template->evaluationStats = $this->evaluation->getEvaluationStats()->fetch();
        $this->template->evaluations = $this->evaluation->getEvaluations();
    }

    public function renderAdmin()
    {
        $this->checkAccess();

        $studentId = $this->user->getId();
        $classId = $this->user->getIdentity()->classId;

        $this->template->evaluationStats = $this->evaluation->getStudentEvaluationStatsByClass($studentId, $classId)->fetch();
        $this->template->evaluations = $this->evaluation->getStudentEvaluationsByClass($studentId, $classId);
    }

    public function renderEdit($EvaluationId)
    {
        $this->checkAccess();

        $studentId = $this->user->getId();
        $evaluation = $this->evaluation->getStudentEvaluation($EvaluationId, $studentId)->fetch();

        if (!$evaluation)
        {
            $this->flashMessage('Hodnocení nenalezeno.','danger');
            $this->redirect('Evaluation:admin');
        }

        $evaluation->Date = $evaluation->Date->format('Y-m-d');
        $this->template->evaluation = $evaluation;
        $this['evaluationForm']->setDefaults($evaluation);
    }

    public function actionDelete($EvaluationId)
    {
        $this->checkAccess();

        $this->transaction->startTransaction();
        $this->evaluation->deleteEvaluation($EvaluationId);
        $this->transaction->endTransaction();

        $this->flashMessage('Hodnocení hodiny úspěšně smazáno.','success');
        $this->redirect('Evaluation:admin');
    }

    protected function createComponentEvaluationForm(): Form
    {
        $studentId = $this->user->getId();
        $classId = $this->user->getIdentity()->classId;

        $attendances = $this->evaluation->getRemainingClassForEvaluation($studentId, $classId)->fetchPairs('Id', 'LessonName');

        $form = new Form;

        $form->addText('Id');

        $form->addText('StarsCount')
            ->addRule($form::RANGE, 'Hodnocení může být nejméně %d a nejvíce %d', [1, 5]);

        $form->addTextArea('Description')
            ->setRequired();

        $form->addSelect('AttendanceId')
            ->setItems($attendances)
            ->setRequired();

        $form->addSubmit('send');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'evaluationFormSucceeded'];

        return $form;
    }

    public function evaluationFormSucceeded(Form $form, \stdClass $values): void
    {
        $values = Utils::convertEmptyToNull($form->values);
        $this->transaction->startTransaction();
        if ($values->Id)
        {
            try {
                $this->evaluation->updateEvaluation($values);
            } catch (\Nette\InvalidArgumentException  $invalidArgumentException) {
                $this->flashMessage('Nelze změnit hodnocení hodiny na již hodnocenou hodinu.' ,"danger");
                $this->redirect('Evaluation:admin');
            }

            $this->flashMessage('Hodnocení hodiny úspěšně upraveno.','success');
        } else {
            $this->evaluation->insertEvaluation($values);
            $this->flashMessage('Hodnocení hodiny úspěšně přidáno.','success');
        }

        $this->transaction->endTransaction();
        $this->redirect('Evaluation:admin');
    }

    private function checkAccess()
    {
        if (!$this->getUser()->isInRole('Student')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.','danger');
            $this->redirect('Homepage:default');
        }
    }

}