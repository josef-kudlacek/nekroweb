<?php


namespace App\Presenters;

use App\Model;
use App\Utils\Utils;
use Nette\Application\UI\Form;

class HomeworkPresenter extends BasePresenter
{
    private $homework;

    /** @var Model\Assessment
     * @inject
     */
    public $assessment;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;


    public function __construct(Model\Homework $homework)
    {
        $this->homework = $homework;
    }

    protected function startup()
    {
        parent::startup();
        if (!$this->getUser()->isInRole('Profesor')) {
            $this->flashMessage('Přístup do neoprávněné sekce. Proběhlo přesměrování na hlavní stránku.', 'danger');
            $this->redirect('Homepage:default');
        }
    }

    public function actionShow()
    {
        $semesterId = $this->user->getIdentity()->semesterId;
        $this->template->assessments = $this->assessment->getAssessmentsBySemester($semesterId);
    }

    public function renderEdit($assessmentId)
    {

    }

    public function actionAdd($assessmentId)
    {
        $semesterId = $this->user->getIdentity()->semesterId;

        $this->transaction->startTransaction();
        $this->assessment->addAssessmentToSemester(array(
            "SemesterId" => $semesterId,
            "AssessmentId" => $assessmentId,
        ));
        $this->transaction->endTransaction();

        $this->flashMessage('Úloha úspěšně přidána do semestru.','success');
        $this->redirect('Homework:show');
    }

    public function actionDelete($assessmentId)
    {
        $semesterId = $this->user->getIdentity()->semesterId;

        $this->transaction->startTransaction();
        $this->assessment->removeAssessmentFromSemester($assessmentId, $semesterId);
        $this->transaction->endTransaction();

        $this->flashMessage('Úloha úspěšně odebrána do semestru.','success');
        $this->redirect('Homework:show');
    }
}