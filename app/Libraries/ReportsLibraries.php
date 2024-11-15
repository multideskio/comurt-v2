<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\AnamnesesModel;
use App\Models\AppointmentsModel;

class ReportsLibraries
{
    protected $mAnanmanese;
    protected $mAppointments;
    protected $currentUser;

    public function __construct()
    {
        $this->mAnanmanese  = new AnamnesesModel();
        $this->mAppointments = new AppointmentsModel();

        $this->currentUser = $this->mAppointments->getAuthenticatedUser();
    }

    /**
     * Método principal para gerar relatórios.
     * 
     * @param array $params
     * @return array
     */
    public function resultReports(array $params): array
    {
        $type  = $params['type']  ?? 'monthly';  // Define 'monthly' como padrão
        $start = $params['start'] ?? null;
        $end   = $params['end']   ?? null;

        if ($type !== 'compareWithLastWeek' && (!$start || !$end)) {
            throw new \InvalidArgumentException("Os parâmetros 'start' e 'end' são obrigatórios para o tipo de relatório: {$type}");
        }

        switch ($type) {
            case 'annual':
                return $this->anual($start, $end);
            case 'monthly':
                return $this->mensal($start, $end);
            case 'weekly':
                return $this->semanal($start, $end);
            case 'daily':
                return $this->diario($start, $end);
            case 'compareWithLastWeek':
                return $this->compareWithLastWeek();
            default:
                throw new \InvalidArgumentException("Tipo de relatório inválido: {$type}");
        }
    }

    /**
     * Relatório Anual
     * 
     * @param string|null $start
     * @param string|null $end
     * @return array
     */
    private function anual(?string $start, ?string $end): array
    {
        $startYear = (int)date('Y', strtotime($start));
        $endYear = (int)date('Y', strtotime($end));

        $result = [];

        for ($year = $startYear; $year <= $endYear; $year++) {
            $result[] = $this->generateReportByYear($year);
        }

        return $result;
    }

    private function generateReportByYear(int $year): array
    {
        $data = "{$year}";

        return [
            'year' => $data,
            'appointments' => $this->mAppointments
                ->where('id_user', $this->currentUser['id'])
                ->where('status !=', 'cancelled')
                ->like('created_at', $data)
                ->countAllResults(),
            'cancelled' => $this->mAppointments
                ->where('id_user', $this->currentUser['id'])
                ->where('status', 'cancelled')
                ->like('created_at', $data)
                ->countAllResults(),
            'anamneses' => $this->mAnanmanese
                ->where('id_user', $this->currentUser['id'])
                ->like('created_at', $data)
                ->countAllResults(),
            'return' => $this->mAppointments
                ->where('id_user', $this->currentUser['id'])
                ->where('type', 'return')
                ->like('created_at', $data)
                ->countAllResults(),
        ];
    }

    private function mensal(?string $start, ?string $end): array
    {
        $startDate = new \DateTime($start . '-01');
        $endDate = new \DateTime($end . '-01');

        $result = [];

        while ($startDate <= $endDate) {
            $result[] = $this->generateReportByMonth($startDate->format('Y'), $startDate->format('m'));
            $startDate->modify('+1 month');
        }

        return $result;
    }

    private function generateReportByMonth(string $year, string $month): array
    {
        $data = "{$year}-{$month}";

        return [
            'date' => $data,
            'appointments' => $this->mAppointments
                ->where('id_user', $this->currentUser['id'])
                ->where('status !=', 'cancelled')
                ->like('created_at', $data)
                ->countAllResults(),
            'cancelled' => $this->mAppointments
                ->where('id_user', $this->currentUser['id'])
                ->where('status', 'cancelled')
                ->like('created_at', $data)
                ->countAllResults(),
            'anamneses' => $this->mAnanmanese
                ->where('id_user', $this->currentUser['id'])
                ->like('created_at', $data)
                ->countAllResults(),
            'return' => $this->mAppointments
                ->where('id_user', $this->currentUser['id'])
                ->where('type', 'return')
                ->like('created_at', $data)
                ->countAllResults(),
        ];
    }

    private function semanal(?string $start, ?string $end): array
    {
        return $this->getReportByDateRange($start, $end);
    }

    private function diario(?string $start, ?string $end): array
    {
        return $this->getReportByDateRange($start, $end);
    }

    private function getReportByDateRange(string $start, string $end): array
    {
        return [
            [
                'date_range' => "{$start} to {$end}",
                'appointments' => $this->mAppointments
                    ->where('id_user', $this->currentUser['id'])
                    ->where('created_at >=', $start)
                    ->where('created_at <=', $end)
                    ->where('status !=', 'cancelled')
                    ->countAllResults(),
                'cancelled' => $this->mAppointments
                    ->where('id_user', $this->currentUser['id'])
                    ->where('created_at >=', $start)
                    ->where('created_at <=', $end)
                    ->where('status', 'cancelled')
                    ->countAllResults(),
                'anamneses' => $this->mAnanmanese
                    ->where('id_user', $this->currentUser['id'])
                    ->where('created_at >=', $start)
                    ->where('created_at <=', $end)
                    ->countAllResults(),
                'return' => $this->mAppointments
                    ->where('id_user', $this->currentUser['id'])
                    ->where('created_at >=', $start)
                    ->where('created_at <=', $end)
                    ->where('type', 'return')
                    ->countAllResults(),
            ]
        ];
    }

    private function compareWithLastWeek(): array
    {
        $currentMonday = new \DateTime('monday this week');
        $currentSunday = new \DateTime('sunday this week');
        $lastMonday = new \DateTime('monday last week');
        $lastSunday = new \DateTime('sunday last week');

        $currentWeekData = $this->getReportByDateRange($currentMonday->format('Y-m-d'), $currentSunday->format('Y-m-d'));
        $lastWeekData = $this->getReportByDateRange($lastMonday->format('Y-m-d'), $lastSunday->format('Y-m-d'));

        return [
            'current_week' => $currentWeekData[0],
            'last_week' => $lastWeekData[0],
            'comparison' => [
                'appointments_diff' => $currentWeekData[0]['appointments'] - $lastWeekData[0]['appointments'],
                'cancelled_diff' => $currentWeekData[0]['cancelled'] - $lastWeekData[0]['cancelled'],
                'anamneses_diff' => $currentWeekData[0]['anamneses'] - $lastWeekData[0]['anamneses'],
                'return_diff' => $currentWeekData[0]['return'] - $lastWeekData[0]['return']
            ]
        ];
    }
}
