<?php


namespace App\Presenters;

use App\Model;

class AttendancePresenter extends BasePresenter
{
    private $attendance;

    public function __construct(Model\Attendance $attendance)
    {
        $this->attendance = $attendance;
    }

    public function renderShow()
    {
        $userId = $this->user->getId();
        $classId = $this->user->getIdentity()->classId;


        $this->template->attendance = $this->attendance->getAttendanceByStudent($userId, $classId)->fetchAll();
    }

    public function renderClass()
    {
        $classId = $this->user->getIdentity()->classId;

        $this->template->attendance = $this->attendance->getAttendanceByClass($classId)->fetchAll();
    }
}