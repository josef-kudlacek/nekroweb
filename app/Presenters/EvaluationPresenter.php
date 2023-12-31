<?php


namespace App\Presenters;

use App\Model;
use App\Utils\Utils;
use Nette\Application\UI\Form;

class EvaluationPresenter extends BasePresenter
{
    private $evaluation;

    private $attendances;

    /** @var Model\Attendance
     * @inject
     */
    public $attendance;

    /** @var Model\Lesson
     * @inject
     */
    public $lesson;

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

    public function actionEdit($EvaluationId)
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

        $this['evaluationForm'] = $this->createEvaluationForm($evaluation->AttendanceId);
        $this['evaluationForm']->setDefaults($evaluation);
    }

    public function actionDelete($EvaluationId)
    {
        $this->checkAccess();

        $studentId = $this->user->getId();
        $evaluation = $this->evaluation->getStudentEvaluation($EvaluationId, $studentId)->fetch();

        if (!$evaluation)
        {
            $this->flashMessage('Hodnocení nenalezeno.','danger');
            $this->redirect('Evaluation:admin');
        }

        $this->transaction->startTransaction();
        $this->evaluation->deleteEvaluation($EvaluationId);
        $this->transaction->endTransaction();

        $this->flashMessage('Hodnocení hodiny úspěšně smazáno.','success');
        $this->redirect('Evaluation:admin');
    }

    protected function createComponentEvaluationForm(): Form
    {
        return $this->createEvaluationForm(null);
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

    private function createEvaluationForm($attendanceId)
    {
        $studentId = $this->user->getId();
        $classId = $this->user->getIdentity()->classId;

        if (is_null($attendanceId)) {
            $this->attendance = $this->evaluation->getRemainingClassForEvaluation($studentId, $classId)->fetchPairs('Id', 'LessonName');
        } else {
            $this->attendance = $this->evaluation->getStudentEvaluationsByAttendance($attendanceId)->fetchPairs('AttendanceId', 'LessonLongName');
        }

        $form = new Form;

        $form->addText('Id');

        $form->addInteger('StarsCount')
            ->setRequired("Je třeba zvolit hodnocení.");

        $form->addTextArea('Description')
            ->setRequired("Je třeba vyplnit komentář hodnocení.");

        $form->addSelect('AttendanceId')
            ->setItems($this->attendance)
            ->setRequired();

        $form->addSubmit('send');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'evaluationFormSucceeded'];

        return $form;
    }


}