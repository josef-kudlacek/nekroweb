<?php


namespace App\Presenters;

use App\Model;
use App\utils\Utils;
use Nette\Application\UI\Form;

class ActivityPresenter extends BasePresenter
{
    private $activity;

    /** @var Model\ActivityType
     * @inject
     */
    public $activityType;

    /** @var Model\StudyClass
     * @inject
     */
    public $studyClass;

    /** @var Model\Lesson
     * @inject
     */
    public $lesson;

    /** @var Model\Transaction
     * @inject
     */
    public $transaction;

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

    public function actionCreate($ClassId, $LessonId)
    {
        $this->template->class = $this->studyClass->getClassById($ClassId)->fetch();
        $this->template->lesson = $this->lesson->getLessonById($LessonId)->fetch();

        $this->template->students = $this->activity->getStudentsAttendance($ClassId, $LessonId);
    }

    protected function createComponentActivityForm(): Form
    {
        $form = new Form;

        $form->addInteger('Question');

        $form->addInteger('RPG');

        $form->addInteger('Discussion');

        $form->addInteger('YearCompetition');

        $form->addInteger('Spell');

        $form->addInteger('ExamDeath');

        $form->addInteger('Rememberall');

        $form->addInteger('Mistake');

        $form->addSubmit('send', 'Zapsat');

        $form->addProtection();

        $form->onError[] = array($this, 'errorForm');
        $form->onSuccess[] = [$this, 'activityFormSucceeded'];

        return $form;
    }

    public function activityFormSucceeded(Form $form, \stdClass $values): void
    {
        $values = $form->getHttpData($form::DATA_TEXT);
        $values = Utils::convertEmptyToNull($values);
        $values = $this->prepareActivityData($values);

        $this->transaction->startTransaction();
        $this->activity->insertActivity($values);
        $this->transaction->endTransaction();

        $this->redirect('Attendance:admin');
    }

    protected function prepareActivityData($values)
    {
        $studentIds = array_keys($values['AttendanceId']);
        $activityTypes = $this->activityType->getActivityTypes()->fetchPairs('Id', 'Label');
        $activityData = array();


        foreach ($studentIds as $student)
        {
            foreach ($activityTypes as $key => $value)
            {
                if ($values[$value][$student])
                {
                    $studentActivity = array(
                        "AttendanceId" => $values['AttendanceId'][$student],
                        "ActivityTypeId" => $key,
                        "ActivityPoints" => $values[$value][$student],
                    );

                    array_push($activityData, $studentActivity);
                }
            }
        }

        return $activityData;
    }

}