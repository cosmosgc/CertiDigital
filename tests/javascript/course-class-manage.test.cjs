const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { test } = require('node:test');

const source = fs.readFileSync(path.join(__dirname, '../../resources/views/course-classes/manage.blade.php'), 'utf8');

// Exercise the actual inline JavaScript without a database or rendered Laravel routes.
function fragment(start, end) {
    const startIndex = source.indexOf(start);
    const endIndex = source.indexOf(end, startIndex);
    assert(startIndex >= 0 && endIndex > startIndex, 'Expected script fragment must exist');
    return source.slice(startIndex, endIndex)
        .replace(/@json\(__\('([^']*)'\)\)/g, (_, text) => JSON.stringify(text))
        .replace(/\{\{.*?\}\}/g, '/test/__ID__');
}

test('recurring suggestions extend past May 2027 without shifting weekdays', () => {
    const originalTimezone = process.env.TZ;
    try {
        for (const timezone of ['UTC', 'America/Sao_Paulo', 'Pacific/Auckland']) {
            process.env.TZ = timezone;
            const context = vm.createContext({ today: '2026-11-25' });
            vm.runInContext(fragment('function parseDate(', 'function calculatePlannedHours('), context);
            const event = {
                start_date: '2026-05-01',
                end_date: '2026-05-31',
                is_recurring_weekly: true,
                weekday: 1,
            };
            const dates = context.getEventOccurrences(event);
            assert(dates.includes('2027-06-07'), timezone);
            assert(dates.includes('2027-11-22'), timezone);
            assert(!dates.includes('2027-11-29'), timezone);
            assert(dates.every(date => context.parseDate(date).getDay() === 1), timezone);
            assert.equal(context.fmtDate(context.getSuggestionWindowEndDate({ ...event, end_date: null })), '2027-11-25');
            assert.equal(context.fmtDate(context.getSuggestionWindowEndDate({ ...event, end_date: '2028-02-29' })), '2029-02-28');
            assert.equal(context.getEventOccurrences({ ...event, is_recurring_weekly: false }).join(','), '2026-05-01');
        }
    } finally {
        if (originalTimezone === undefined) delete process.env.TZ;
        else process.env.TZ = originalTimezone;
    }
});

function creationFlow(refresh) {
    let handler;
    const alerts = [];
    const context = vm.createContext({
        document: { getElementById: () => ({ addEventListener: (_, callback) => { handler = callback; } }) },
        token: 'test', currentUserId: 1, classId: 4,
        selectedAttendanceId: null, attendanceListExpanded: false,
        attendanceList: { classList: { remove() {} } },
        fetch: async () => ({ ok: true, json: async () => ({ id: 123 }) }),
        fetchClassData: refresh,
        alert: message => alerts.push(message),
    });
    vm.runInContext(fragment("document.getElementById('upcomingClassesList')?.addEventListener", 'async function init()'), context);
    const button = { disabled: false, dataset: { date: '2027-06-07', duration: '1', title: 'Class' } };
    return { context, button, alerts, click: () => handler({ target: { closest: () => button } }) };
}

test('repeated clicks submit once and select the created attendance', async () => {
    const flow = creationFlow(async () => true);
    let calls = 0;
    let resolve;
    flow.context.fetch = () => {
        calls++;
        return new Promise(done => { resolve = done; });
    };
    const pending = flow.click();
    await flow.click();
    assert.equal(calls, 1);
    resolve({ ok: true, json: async () => ({ id: 123 }) });
    await pending;
    assert.equal(flow.context.selectedAttendanceId, 123);
    assert.equal(flow.context.attendanceListExpanded, true);
    assert.equal(flow.button.disabled, true);
    assert.equal(flow.alerts.length, 0);
});

test('successful creation followed by failed refresh warns and keeps the button disabled', async () => {
    for (const refresh of [async () => false, async () => { throw new Error('Network unavailable'); }]) {
        const flow = creationFlow(refresh);
        await flow.click();
        assert.equal(flow.button.disabled, true);
        assert.equal(flow.alerts.length, 1);
        assert.match(flow.alerts[0], /Sessão criada/);
    }
});

test('API validation failure displays its error and allows correction', async () => {
    const flow = creationFlow(async () => { assert.fail('Should not refresh after rejected creation'); });
    flow.context.fetch = async () => ({ ok: false, status: 422, json: async () => ({ message: 'Invalid date' }) });
    await flow.click();
    assert.equal(flow.button.disabled, false);
    assert.deepEqual(flow.alerts, ['Invalid date']);
});

test('ambiguous creation failures require a reload before another submission', async () => {
    for (const failure of [
        async () => { throw new Error('Connection lost'); },
        async () => ({ ok: false, status: 500, json: async () => ({ message: 'Server error' }) }),
        async () => ({ ok: false, status: 408, json: async () => ({}) }),
    ]) {
        const flow = creationFlow(async () => { assert.fail('Should not refresh an unconfirmed creation'); });
        let calls = 0;
        flow.context.fetch = async () => { calls++; return failure(); };
        await flow.click();
        await flow.click();
        assert.equal(calls, 1);
        assert.equal(flow.button.disabled, true);
        assert.equal(flow.alerts.length, 1);
        assert.match(flow.alerts[0], /Recarregue/);
    }
});

test('schedule saves distinguish a failed request from a failed refresh', async () => {
    for (const saved of [false, true]) {
        let handler;
        const alerts = [];
        const button = { disabled: false };
        const card = {
            dataset: { eventId: '1' },
            querySelector: () => ({ value: '', checked: true }),
        };
        const context = vm.createContext({
            document: { getElementById: () => ({ addEventListener: (_, callback) => { handler = callback; } }) },
            token: 'test', currentUserId: 1,
            fetch: async () => {
                if (!saved) throw new Error('Connection lost');
                return { ok: true };
            },
            fetchClassData: async () => false,
            alert: message => alerts.push(message),
        });
        vm.runInContext(fragment(
            "document.getElementById('scheduleEventsList')?.addEventListener",
            "document.getElementById('upcomingClassesList')?.addEventListener"
        ), context);
        await handler({
            target: { closest: selector => selector === '[data-event-id]' ? card : selector === '.saveScheduleEventBtn' ? button : null },
        });
        assert.equal(button.disabled, false);
        assert.equal(alerts.length, 1);
        assert.match(alerts[0], saved ? /Evento salvo/ : /confirmar o salvamento/);
    }
});

test('refresh destroys the previous DataTable before replacing its rows', () => {
    const operations = [];
    const context = {
        classData: { id: 4, name: 'Class', enrollments: [] },
        studentsTable: null,
        studentsTableBody: { set innerHTML(value) { operations.push('replace rows'); } },
        studentSearchInput: { value: '' },
        DataTable: function () {
            operations.push('initialize');
            this.destroy = () => operations.push('destroy');
        },
        formatHours: value => `${value}h`,
    };
    for (const name of ['className', 'classMeta', 'courseTitle', 'instructorName', 'workloadHours', 'classDescription', 'studentCount']) {
        context[name] = {};
    }
    for (const name of ['displayPlannedHours', 'renderStudentSearchResults', 'renderAttendanceList', 'renderSelectedAttendance', 'renderUpcomingClasses', 'renderScheduleEvents']) {
        context[name] = () => {};
    }
    vm.createContext(context);
    vm.runInContext(fragment('function renderClassData()', 'const toggleAttendanceListBtn'), context);
    context.renderClassData();
    context.renderClassData();
    assert.deepEqual(operations, ['replace rows', 'initialize', 'destroy', 'replace rows', 'initialize']);
});

test('saved session names and student options render markup as text', () => {
    const name = '<img src=x onerror="alert(1)"> & "Class"';
    const email = 'student<tag>@example.com';
    const context = vm.createContext({
        selectedAttendanceId: null,
        attendanceShowBaseUrl: '/attendance/__ATTENDANCE__',
        formatSessionDate: value => value,
        formatHours: value => `${value}h`,
        getSelectedAttendance: () => null,
        classData: { enrollments: [{ student_id: 7, student: { full_name: name, email } }] },
        attendanceRecordForm: { student_id: { innerHTML: '' } },
    });
    vm.runInContext(fragment('function escapeHtml(', 'function setSelectedEnrollmentStudent('), context);
    vm.runInContext(fragment('function attendanceCardHTML(', 'function renderAttendanceList('), context);
    vm.runInContext(fragment('function populateAttendanceRecordOptions(', 'async function loadStudents('), context);
    const html = context.attendanceCardHTML({
        id: 1, name, attendance_date: '2027-06-07', duration_hours: 1, records: [],
    });
    assert(!html.includes('<img'));
    assert(html.includes('&lt;img src=x onerror=&quot;alert(1)&quot;&gt; &amp; &quot;Class&quot;'));
    context.populateAttendanceRecordOptions();
    const options = context.attendanceRecordForm.student_id.innerHTML;
    assert(!options.includes('<img'));
    assert(!options.includes('<tag>'));
    assert(options.includes('student&lt;tag&gt;@example.com'));
});
