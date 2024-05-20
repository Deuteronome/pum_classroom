<?php

namespace App\Service;

use App\Repository\StudentGroupRepository;

class RegistrationCodeService 
{
    private $studentGroupRepository;

    public function __construct(StudentGroupRepository $studentGroupRepository)
    {
        $this->studentGroupRepository = $studentGroupRepository;
    }
    public function generateGroupCode()
    {
        $start = 0;
        $end = 10;
        $Strings = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $code = substr(str_shuffle($Strings), $start, $end);

        $groupTest = $this->studentGroupRepository->findOneByCode($code);        

        while ($groupTest !== null) 
        {
            $code = substr(str_shuffle($Strings), $start, $end);

            $groupTest = $this->studentGroupRepository->findByCode($code);
        }

        return $code;
    }
}