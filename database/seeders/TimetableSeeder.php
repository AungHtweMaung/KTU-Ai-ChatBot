<?php

namespace Database\Seeders;

use App\Models\Major;
use App\Models\MajorYear;
use App\Models\Timetable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Registers the timetable images that live in storage/app/public/timetables.
 * The images are photographs of the departments' printed timetables; they
 * survive a database reset but their rows do not, so they are re-registered
 * here.
 *
 * Entries are grouped by department code and are [year_number, semester,
 * filename]. Year 6 is the Master (M.E/M.S) class, and `semester` is the
 * semester within the year (1 or 2), matching the admin panel: the Civil
 * "Year II, Semester IV" sheet is year 2, semester 2.
 *
 * A class can have more than one sheet for the same semester, so these are not
 * unique on (class, semester): Civil first year is taught in two sections, EP
 * has separate re-take and pre-requisite sheets, and MC fifth year has two
 * prints of its second-semester sheet (`-alt` is the shorter one).
 *
 * Keyed on the image path, so a file maps to exactly one row and the seeder is
 * safe to run repeatedly. Entries whose image or class is missing are skipped
 * rather than registered broken.
 */
class TimetableSeeder extends Seeder
{
    /** @var array<string, list<array{int, int, string}>> */
    private const TIMETABLES = [
        'CEIT' => [
            [1, 2, 'ceit-year1-sem2.jpg'],
            [2, 2, 'ceit-year2-sem2.jpg'],
            [3, 2, 'ceit-year3-sem2.jpg'],
            [4, 2, 'ceit-year4-sem2.jpg'],
            [5, 1, 'ceit-year5-sem1.jpg'],
            [5, 2, 'ceit-year5-sem2.jpg'],
            [6, 2, 'ceit-me-sem2.jpg'],
        ],
        'Civil' => [
            [1, 2, 'civil-year1-sem2-a.jpg'],
            [1, 2, 'civil-year1-sem2-b.jpg'],
            [2, 2, 'civil-year2-sem2.jpg'],
            [3, 2, 'civil-year3-sem2.jpg'],
            [4, 2, 'civil-year4-sem2.jpg'],
            [5, 1, 'civil-year5-sem1.jpg'],
            [5, 2, 'civil-year5-sem2.jpg'],
            [6, 2, 'civil-me-sem2.jpg'],
        ],
        'EP' => [
            [1, 2, 'ep-year1-sem2.jpg'],
            [1, 2, 'ep-year1-sem2-retake.jpg'],
            [2, 1, 'ep-year2-sem1-prerequisite.jpg'],
            [2, 2, 'ep-year2-sem2.jpg'],
            [3, 2, 'ep-year3-sem2.jpg'],
            [4, 2, 'ep-year4-sem2.jpg'],
            [5, 1, 'ep-year5-sem1.jpg'],
            [5, 2, 'ep-year5-sem2.jpg'],
            [6, 2, 'ep-me-sem2.jpg'],
        ],
        'EC' => [
            [1, 2, 'ec-year1-sem2.jpg'],
            [2, 2, 'ec-year2-sem2.jpg'],
            [3, 2, 'ec-year3-sem2.jpg'],
            [4, 2, 'ec-year4-sem2.jpg'],
            [5, 1, 'ec-year5-sem1.jpg'],
            [5, 2, 'ec-year5-sem2.jpg'],
            [6, 2, 'ec-me-sem2.jpg'],
        ],
        'ME' => [
            [1, 2, 'mech-year1-sem2.jpg'],
            [2, 2, 'mech-year2-sem2.jpg'],
            [3, 2, 'mech-year3-sem2.jpg'],
            [4, 2, 'mech-year4-sem2.jpg'],
            [5, 1, 'mech-year5-sem1.jpg'],
            [5, 2, 'mech-year5-sem2.jpg'],
            [6, 2, 'mech-me-sem2.jpg'],
        ],
        'MC' => [
            [1, 2, 'mc-year1-sem2.jpg'],
            [2, 2, 'mc-year2-sem2.jpg'],
            [3, 2, 'mc-year3-sem2.jpg'],
            [4, 2, 'mc-year4-sem2.jpg'],
            [5, 1, 'mc-year5-sem1.jpg'],
            [5, 2, 'mc-year5-sem2.jpg'],
            [5, 2, 'mc-year5-sem2-alt.jpg'],
        ],
        'Met' => [
            [1, 2, 'met-year1-sem2.jpg'],
            [2, 2, 'met-year2-sem2.jpg'],
            [3, 2, 'met-year3-sem2.jpg'],
            [4, 2, 'met-year4-sem2.jpg'],
            [5, 1, 'met-year5-sem1.jpg'],
            [5, 2, 'met-year5-sem2.jpg'],
            [6, 2, 'met-me-sem2.jpg'],
        ],
        'NT' => [
            [1, 2, 'nt-year1-sem2.jpg'],
            [2, 2, 'nt-year2-sem2.jpg'],
            [3, 2, 'nt-year3-sem2.jpg'],
            [4, 2, 'nt-year4-sem2.jpg'],
            [5, 1, 'nt-year5-sem1.jpg'],
            [5, 2, 'nt-year5-sem2.jpg'],
        ],
        'BioT' => [
            [1, 2, 'biot-year1-sem2.jpg'],
            [2, 2, 'biot-year2-sem2.jpg'],
            [3, 2, 'biot-year3-sem2.jpg'],
            [4, 2, 'biot-year4-sem2.jpg'],
            [6, 2, 'biot-me-sem2.jpg'],
        ],
    ];

    public function run(): void
    {
        foreach (self::TIMETABLES as $departmentCode => $entries) {
            $major = Major::whereHas('department', fn ($q) => $q->where('code', $departmentCode))->first();

            if (! $major) {
                $this->command?->warn("TimetableSeeder: no {$departmentCode} major found; skipped.");

                continue;
            }

            foreach ($entries as [$yearNumber, $semester, $filename]) {
                $path = 'timetables/'.$filename;

                if (! Storage::disk('public')->exists($path)) {
                    $this->command?->warn("TimetableSeeder: missing image {$path}; skipped.");

                    continue;
                }

                $majorYear = MajorYear::where('major_id', $major->id)
                    ->where('year_number', $yearNumber)
                    ->first();

                if (! $majorYear) {
                    $this->command?->warn("TimetableSeeder: no year {$yearNumber} for {$major->name}; skipped.");

                    continue;
                }

                Timetable::updateOrCreate(
                    ['image_path' => $path],
                    ['major_year_id' => $majorYear->id, 'semester' => $semester],
                );
            }
        }
    }
}
