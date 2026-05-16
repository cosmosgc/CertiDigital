@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('course-classes.show', ['courseClass' => $courseClass]) }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; {{ __('Voltar para a turma') }}</a>
                <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ __('Relatório de Desempenho') }}</h1>
                <p class="text-sm text-gray-500" id="reportHeader">{{ $courseClass->course?->title }} &mdash; {{ $courseClass->name }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('course-classes.performance-report.export-xlsx', $courseClass) }}" class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm">{{ __('Exportar XLSX') }}</a>
                <button id="printReport" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm">{{ __('Imprimir') }}</button>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
            <div id="reportContainer" class="p-1 min-w-max">
                <div class="flex items-center justify-center py-12 text-gray-400">
                    <svg class="animate-spin h-8 w-8 mr-3" viewBox="0 0 24 24">{{ __('Carregando...') }}</svg>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const courseClassId = @json($courseClassId);
const container = document.getElementById('reportContainer');
const printBtn = document.getElementById('printReport');
const apiUrl = "{{ route('api.course-classes.performance-report', $courseClassId) }}";

const TRIMESTER_LABELS = {
    1: '{{ __("1º Trimestre") }}',
    2: '{{ __("2º Trimestre") }}',
    3: '{{ __("3º Trimestre") }}',
    4: '{{ __("4º Trimestre") }}',
};

function getTrimester(month) {
    if (month <= 3) return 1;
    if (month <= 6) return 2;
    if (month <= 9) return 3;
    return 4;
}

function formatDate(value) {
    if (!value) return '-';
    const d = new Date(value + 'T12:00:00');
    return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
}

function formatFullDate(value) {
    if (!value) return '-';
    const d = new Date(value + 'T12:00:00');
    return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function n(value) {
    const v = Number(value);
    return isNaN(v) ? '-' : v.toFixed(2).replace('.', ',');
}

function pct(value) {
    const v = Number(value);
    return isNaN(v) ? '-' : v.toFixed(2).replace('.', ',') + '%';
}

function escHtml(value) {
    return String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');
}

function groupByTrimester(items, key) {
    const groups = {};
    for (const item of items) {
        const t = key(item);
        if (!groups[t]) groups[t] = [];
        groups[t].push(item);
    }
    return groups;
}

async function loadReport() {
    const res = await fetch(apiUrl, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
    });
    const data = await res.json();
    renderReport(data);
}

function renderReport(data) {
    const dates = data.attendance_dates || [];
    const students = data.students || [];
    const cls = data.class || {};

    document.getElementById('reportHeader').textContent =
        (cls.course?.title || '') + ' — ' + (cls.name || '');

    const datesByTrimester = groupByTrimester(dates, d => d.trimester);

    let html = '<table class="w-full border-collapse text-xs">';

    // === HEADER ROW 1: Class Name + Trimester labels ===
    html += '<thead>';
    html += '<tr class="bg-gray-100">';
    html += '<th class="border border-gray-300 p-1.5 text-left font-semibold sticky left-0 bg-gray-100 z-10" colspan="4">{{ __("Aluno") }}</th>';
    html += '<th class="border border-gray-300 p-1.5 text-left font-semibold" colspan="2">{{ __("Período") }}</th>';

    // Trimester date columns
    const trimesterKeys = Object.keys(datesByTrimester).map(Number).sort();
    for (const t of trimesterKeys) {
        const atts = datesByTrimester[t];
        html += '<th class="border border-gray-300 p-1.5 text-center font-semibold bg-indigo-50" colspan="' + atts.length + '">' + TRIMESTER_LABELS[t] + '</th>';
    }

    // Frequency header
    html += '<th class="border border-gray-300 p-1.5 text-center font-semibold bg-emerald-50" colspan="3">{{ __("Frequência") }}</th>';

    // Per-trimester grade blocks
    for (const t of trimesterKeys) {
        html += '<th class="border border-gray-300 p-1.5 text-center font-semibold bg-amber-50" colspan="10">' + TRIMESTER_LABELS[t] + ' — {{ __("Notas") }}</th>';
    }

    html += '</tr>';

    // === HEADER ROW 2: Subheaders ===
    html += '<tr class="bg-gray-50 text-gray-600">';
    html += '<th class="border border-gray-300 p-1 font-medium">N</th>';
    html += '<th class="border border-gray-300 p-1 font-medium text-left sticky left-0 bg-gray-50 z-10">{{ __("Nome") }}</th>';
    html += '<th class="border border-gray-300 p-1 font-medium">ID</th>';
    html += '<th class="border border-gray-300 p-1 font-medium">{{ __("Idade") }}</th>';
    html += '<th class="border border-gray-300 p-1 font-medium">{{ __("Início") }}</th>';
    html += '<th class="border border-gray-300 p-1 font-medium">{{ __("Fim") }}</th>';

    // Date subheaders per trimester
    for (const t of trimesterKeys) {
        const atts = datesByTrimester[t];
        for (const a of atts) {
            html += '<th class="border border-gray-300 p-0.5 text-center font-normal text-gray-500 text-[10px] leading-tight">' + formatDate(a.date) + '</th>';
        }
    }

    // Frequency subheaders
    html += '<th class="border border-gray-300 p-1 font-medium text-emerald-700">{{ __("Pres.") }}</th>';
    html += '<th class="border border-gray-300 p-1 font-medium text-rose-700">{{ __("Falta") }}</th>';
    html += '<th class="border border-gray-300 p-1 font-medium">{{ __("%") }}</th>';

    // Grade subheaders per trimester
    for (const t of trimesterKeys) {
        html += '<th class="border border-gray-300 p-1 font-medium">Média<br>Ativ</th>';
        html += '<th class="border border-gray-300 p-1 font-medium">Ativ 1</th>';
        html += '<th class="border border-gray-300 p-1 font-medium">Ativ 2</th>';
        html += '<th class="border border-gray-300 p-1 font-medium">Ativ 3</th>';
        html += '<th class="border border-gray-300 p-1 font-medium">Média<br>AU</th>';
        html += '<th class="border border-gray-300 p-1 font-medium">AU 1</th>';
        html += '<th class="border border-gray-300 p-1 font-medium">AU 2</th>';
        html += '<th class="border border-gray-300 p-1 font-medium">AU 3</th>';
        html += '<th class="border border-gray-300 p-1 font-medium">Nota<br>Final</th>';
        html += '<th class="border border-gray-300 p-1 font-medium">Freq<br>Trim</th>';
    }

    html += '</tr>';
    html += '</thead>';

    // === BODY ===
    html += '<tbody>';
    students.forEach((s, idx) => {
        const rowAtts = s.attendances || [];
        const attsByT = groupByTrimester(rowAtts, a => a.trimester);
        const summariesByT = {};
        (s.trimester_summaries || []).forEach(sum => { summariesByT[sum.trimester] = sum; });
        const tgByT = {};
        (s.trimester_grades || []).forEach(tg => { tgByT[tg.trimester] = tg; });
        const freq = s.overall_frequency || {};

        html += '<tr class="hover:bg-gray-50">';
        html += '<td class="border border-gray-200 p-1.5 text-center text-gray-500">' + (idx + 1) + '</td>';
        html += '<td class="border border-gray-200 p-1.5 font-medium text-gray-900 sticky left-0 bg-white z-10">' + escHtml(s.full_name) + '</td>';
        html += '<td class="border border-gray-200 p-1.5 text-center">' + s.id + '</td>';

        // Age from birth_date
        let ageHtml = '-';
        if (s.birth_date) {
            const birth = new Date(s.birth_date + 'T12:00:00');
            const today = new Date();
            let age = today.getFullYear() - birth.getFullYear();
            const m = today.getMonth() - birth.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
            ageHtml = age + ' {{ __("anos") }}';
        }
        html += '<td class="border border-gray-200 p-1.5 text-center">' + ageHtml + '</td>';

        html += '<td class="border border-gray-200 p-1.5 text-center">' + formatFullDate(s.period_start) + '</td>';
        html += '<td class="border border-gray-200 p-1.5 text-center">' + formatFullDate(s.period_end) + '</td>';

        // Attendance per date
        for (const t of trimesterKeys) {
            const atts = datesByTrimester[t];
            for (const da of atts) {
                const found = rowAtts.find(a => a.attendance_id === da.id);
                const present = found ? found.present : false;
                const grade = found && found.grade != null ? found.grade : null;
                let cellClass = 'border border-gray-200 p-0.5 text-center text-[10px] ';
                cellClass += present ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-400';
                let cellText = present ? 'P' : 'F';
                if (grade !== null) {
                    cellText += '<br>' + n(grade);
                }
                html += '<td class="' + cellClass + '">' + cellText + '</td>';
            }
        }

        // Frequency summary
        const freqPct = Number(freq.frequency_pct);
        let freqPctClass = 'border border-gray-200 p-1.5 text-center font-semibold ';
        if (freqPct >= 75) freqPctClass += 'text-emerald-700';
        else if (freqPct >= 50) freqPctClass += 'text-amber-700';
        else freqPctClass += 'text-rose-700';

        html += '<td class="border border-gray-200 p-1.5 text-center font-semibold text-emerald-700">' + (freq.present_count || 0) + '</td>';
        html += '<td class="border border-gray-200 p-1.5 text-center font-semibold text-rose-700">' + (freq.absent_count || 0) + '</td>';
        html += '<td class="' + freqPctClass + '">' + pct(freq.frequency_pct) + '</td>';

        // Per trimester grades
        for (const t of trimesterKeys) {
            const tg = tgByT[t] || {};

            html += '<td class="border border-gray-200 p-1 text-center">' + n(tg.activities_average) + '</td>';
            html += '<td class="border border-gray-200 p-1 text-center">' + n(tg.activity_grade_1) + '</td>';
            html += '<td class="border border-gray-200 p-1 text-center">' + n(tg.activity_grade_2) + '</td>';
            html += '<td class="border border-gray-200 p-1 text-center">' + n(tg.activity_grade_3) + '</td>';
            html += '<td class="border border-gray-200 p-1 text-center">' + n(tg.au_average) + '</td>';
            html += '<td class="border border-gray-200 p-1 text-center">' + n(tg.au_grade_1) + '</td>';
            html += '<td class="border border-gray-200 p-1 text-center">' + n(tg.au_grade_2) + '</td>';
            html += '<td class="border border-gray-200 p-1 text-center">' + n(tg.au_grade_3) + '</td>';
            html += '<td class="border border-gray-200 p-1 text-center font-bold ' + ((tg.final_grade ?? 0) >= 6 ? 'text-emerald-700' : 'text-rose-700') + '">' + n(tg.final_grade) + '</td>';

            // Trimester frequency
            const tSum = summariesByT[t] || {};
            html += '<td class="border border-gray-200 p-1 text-center text-[10px]">' + pct(tSum.frequency_pct) + '</td>';
        }

        html += '</tr>';
    });

    // === SUMMARY ROW ===
    html += '<tr class="bg-gray-100 font-semibold">';
    html += '<td class="border border-gray-300 p-1.5 text-center" colspan="4">{{ __("Média da Turma") }}</td>';
    html += '<td class="border border-gray-300 p-1.5" colspan="2"></td>';

    // Attendance summaries per date
    for (const t of trimesterKeys) {
        const atts = datesByTrimester[t];
        for (const da of atts) {
            const presentCount = students.filter(s => {
                const found = (s.attendances || []).find(a => a.attendance_id === da.id);
                return found && found.present;
            }).length;
            const total = students.length;
            const pctVal = total > 0 ? Math.round((presentCount / total) * 100) : 0;
            let cls = 'border border-gray-200 p-0.5 text-center text-[10px] ';
            cls += pctVal >= 75 ? 'bg-emerald-100 text-emerald-700' : pctVal >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700';
            html += '<td class="' + cls + '">' + pctVal + '%</td>';
        }
    }

    // Average frequency
    const avgFreq = students.reduce((sum, s) => sum + (Number(s.overall_frequency?.frequency_pct) || 0), 0) / (students.length || 1);
    html += '<td class="border border-gray-300 p-1.5 text-center" colspan="3">' + pct(avgFreq) + '</td>';

    // Average grades per trimester
    for (const t of trimesterKeys) {
        const grades = students.map(s => {
            const tg = (s.trimester_grades || []).find(g => g.trimester === t);
            return tg ? Number(tg.final_grade) : null;
        }).filter(v => v !== null);
        const avg = grades.length > 0 ? grades.reduce((a, b) => a + b, 0) / grades.length : null;
        html += '<td class="border border-gray-300 p-1 text-center font-bold" colspan="9">' + (avg !== null ? n(avg) : '-') + '</td>';
        html += '<td class="border border-gray-300 p-1 text-center"></td>';
    }

    html += '</tr>';
    html += '</tbody>';
    html += '</table>';

    container.innerHTML = html;
}

printBtn.addEventListener('click', () => window.print());

loadReport();
</script>

<style>
@media print {
    body * { visibility: hidden; }
    #reportContainer, #reportContainer * { visibility: visible; }
    #reportContainer { position: absolute; left: 0; top: 0; width: 100%; }
    nav, .sidebar, button:not(#reportContainer button) { display: none !important; }
    #reportContainer table { font-size: 8px !important; }
    #reportContainer th, #reportContainer td { padding: 2px !important; }
    .sticky { position: static !important; }
}
</style>
@endsection
