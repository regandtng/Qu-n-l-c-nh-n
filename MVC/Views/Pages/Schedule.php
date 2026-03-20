<link rel="stylesheet" href="/Test/Public/Css/schedule.css">
<div class="Page-Schedule">
 
    <div class="head">
        <button onclick="prevMonth()">❮</button>
        <div class="head-title">
            <h2 id="monthYear"></h2>
            <span id="lunarMonthYear"></span>
        </div>
        <button onclick="nextMonth()">❯</button>
    </div>
 
    <div class="week">
        <div>T2</div>
        <div>T3</div>
        <div>T4</div>
        <div>T5</div>
        <div>T6</div>
        <div>T7</div>
        <div class="cn">CN</div>
    </div>
 
    <div class="day" id="calendarDays"></div>
 
</div>
 
<script>
    let currentDate = new Date();
 
    // =============================================
    // THUẬT TOÁN ÂM LỊCH (Hồ Ngọc Đức)
    // =============================================
    function jdFromDate(dd, mm, yy) {
        var a = Math.floor((14 - mm) / 12);
        var y = yy + 4800 - a;
        var m = mm + 12 * a - 3;
        var jd = dd + Math.floor((153 * m + 2) / 5) + 365 * y
            + Math.floor(y / 4) - Math.floor(y / 100) + Math.floor(y / 400) - 32045;
        if (jd < 2299161)
            jd = dd + Math.floor((153 * m + 2) / 5) + 365 * y + Math.floor(y / 4) - 32083;
        return jd;
    }
 
    function getNewMoonDay(k, tz) {
        var T = k / 1236.85, T2 = T * T, T3 = T2 * T, dr = Math.PI / 180;
        var Jd1 = 2415020.75933 + 29.53058868 * k + 0.0001178 * T2 - 0.000000155 * T3
                + 0.00033 * Math.sin((166.56 + 132.87 * T - 0.009173 * T2) * dr);
        var M   = 359.2242  + 29.10535608  * k - 0.0000333 * T2 - 0.00000347 * T3;
        var Mpr = 306.0253  + 385.81691806 * k + 0.0107306 * T2 + 0.00001236 * T3;
        var F   = 21.2964   + 390.67050646 * k - 0.0016528 * T2 - 0.00000239 * T3;
        var C1  = (0.1734 - 0.000393 * T) * Math.sin(M * dr) + 0.0021 * Math.sin(2 * dr * M)
                - 0.4068 * Math.sin(Mpr * dr) + 0.0161 * Math.sin(2 * dr * Mpr)
                - 0.0004 * Math.sin(3 * dr * Mpr)
                + 0.0104 * Math.sin(2 * dr * F)   - 0.0051 * Math.sin(dr * (M + Mpr))
                - 0.0074 * Math.sin(dr * (M - Mpr)) + 0.0004 * Math.sin(dr * (2*F + M))
                - 0.0004 * Math.sin(dr * (2*F - M)) - 0.0006 * Math.sin(dr * (2*F + Mpr))
                + 0.0010 * Math.sin(dr * (2*F - Mpr)) + 0.0005 * Math.sin(dr * (2*Mpr + M));
        var dt = (T < -11)
            ? 0.001 + 0.000839*T + 0.0002261*T2 - 0.00000845*T3 - 0.000000081*T*T3
            : -0.000278 + 0.000265*T + 0.000262*T2;
        return Math.floor(Jd1 + C1 - dt + 0.5 + tz / 24);
    }
 
    function getSunLongitude(jdn, tz) {
        var T = (jdn - 2451545.5 - tz/24) / 36525, T2 = T*T, dr = Math.PI/180;
        var M  = 357.52910 + 35999.05030*T - 0.0001559*T2 - 0.00000048*T*T2;
        var L0 = 280.46645 + 36000.76983*T + 0.0003032*T2;
        var DL = (1.9146 - 0.004817*T - 0.000014*T2)*Math.sin(dr*M)
               + (0.019993 - 0.000101*T)*Math.sin(2*dr*M) + 0.00029*Math.sin(3*dr*M);
        var L = (L0 + DL - 0.00569 - 0.00478*Math.sin((125.04 - 1934.136*T)*dr)) * dr;
        L -= Math.PI*2 * Math.floor(L / (Math.PI*2));
        return Math.floor(L / Math.PI * 6);
    }
 
    function getLunarMonth11(yy, tz) {
        var k  = Math.floor((jdFromDate(31,12,yy) - 2415021) / 29.530588853);
        var nm = getNewMoonDay(k, tz);
        if (getSunLongitude(nm, tz) >= 9) nm = getNewMoonDay(k-1, tz);
        return nm;
    }
 
    function getLeapMonthOffset(a11, tz) {
        var k = Math.floor((a11 - 2415021.076998695) / 29.530588853 + 0.5);
        var i = 1, last = 0, arc = getSunLongitude(getNewMoonDay(k+i, tz), tz);
        do { last = arc; i++; arc = getSunLongitude(getNewMoonDay(k+i, tz), tz); }
        while (arc !== last && i < 14);
        return i - 1;
    }
 
    function convertSolar2Lunar(dd, mm, yy, tz) {
        var dayNumber  = jdFromDate(dd, mm, yy);
        var k          = Math.floor((dayNumber - 2415021.076998695) / 29.530588853);
        var monthStart = getNewMoonDay(k+1, tz);
        if (monthStart > dayNumber) monthStart = getNewMoonDay(k, tz);
        var a11 = getLunarMonth11(yy, tz), b11 = a11, lunarYear;
        if (a11 >= monthStart) { lunarYear = yy;     a11 = getLunarMonth11(yy-1, tz); }
        else                   { lunarYear = yy + 1; b11 = getLunarMonth11(yy+1, tz); }
        var lunarDay = dayNumber - monthStart + 1;
        var diff = Math.floor((monthStart - a11) / 29);
        var lunarLeap = 0, lunarMonth = diff + 11;
        if (b11 - a11 > 365) {
            var leapOff = getLeapMonthOffset(a11, tz);
            if (diff >= leapOff) { lunarMonth = diff + 10; if (diff === leapOff) lunarLeap = 1; }
        }
        if (lunarMonth > 12) lunarMonth -= 12;
        if (lunarMonth >= 11 && diff < 4) lunarYear -= 1;
        return [lunarDay, lunarMonth, lunarYear, lunarLeap];
    }
 
    // Tên Can Chi
    const CAN  = ["Giáp","Ất","Bính","Đinh","Mậu","Kỷ","Canh","Tân","Nhâm","Quý"];
    const CHI  = ["Tý","Sửu","Dần","Mão","Thìn","Tỵ","Ngọ","Mùi","Thân","Dậu","Tuất","Hợi"];
    function canChi(year) { return CAN[(year+6)%10] + " " + CHI[(year+8)%12]; }
 
    function getLunarDate(d, m, y) { return convertSolar2Lunar(d, m, y, 7); }
 
    // =============================================
    // RENDER
    // =============================================
    function renderCalendar() {
        const daysContainer = document.getElementById("calendarDays");
        const monthYear     = document.getElementById("monthYear");
        const lunarHeader   = document.getElementById("lunarMonthYear");
 
        let year  = currentDate.getFullYear();
        let month = currentDate.getMonth(); // 0-based
 
        // Dương lịch header
        monthYear.innerText = `Tháng ${month + 1} - ${year}`;
 
        // Tính âm lịch của ngày 1 dương để lấy tháng âm đại diện
        let lunarOf1 = getLunarDate(1, month + 1, year);
        let lunarOf15 = getLunarDate(15, month + 1, year);
        // Dùng ngày 15 để lấy tháng âm chính của tháng dương
        lunarHeader.innerHTML =
            `Tháng ${lunarOf15[1]} âm lịch &nbsp;·&nbsp; Năm ${canChi(lunarOf15[2])}`;
 
        // Thứ của ngày 1 (0=CN ... 6=T7)
        // Lịch bắt đầu từ T2 => T2=0, T3=1, ..., T7=5, CN=6
        let rawFirstDay = new Date(year, month, 1).getDay(); // 0=CN
        let firstCol    = (rawFirstDay === 0) ? 6 : rawFirstDay - 1; // đổi sang T2-based
 
        let lastDate = new Date(year, month + 1, 0).getDate();
 
        daysContainer.innerHTML = "";
 
        // Ô trống đầu
        for (let i = 0; i < firstCol; i++)
            daysContainer.innerHTML += `<div class="day-cell empty"></div>`;
 
        let today = new Date();
 
        for (let i = 1; i <= lastDate; i++) {
            let isToday = (i === today.getDate() && month === today.getMonth() && year === today.getFullYear());
 
            let lunar     = getLunarDate(i, month + 1, year);
            let lDay      = lunar[0], lMonth = lunar[1], lLeap = lunar[3];
            let isMung1   = (lDay === 1);
 
            let lunarText = isMung1
                ? `1/${lMonth}${lLeap ? '<span class="leap-tag">n</span>' : ''}`
                : `${lDay}`;
            let lunarClass = isMung1 ? 'lunar-day lunar-first' : 'lunar-day';
 
            // CN = cột 6 (index 6) trong lưới T2-based
            let colIndex     = (firstCol + i - 1) % 7;
            let isCN         = (colIndex === 6);
            let isSat        = (colIndex === 5);
            let weekendClass = (isCN || isSat) ? " weekend" : "";
            let cnClass      = isCN ? " is-cn" : "";
            let todayClass   = isToday ? " today" : "";
 
            daysContainer.innerHTML += `
                <div class="day-cell${todayClass}${weekendClass}${cnClass}">
                    <span class="solar-day">${i}</span>
                    <span class="${lunarClass}">${lunarText}</span>
                </div>`;
        }
    }
 
    function prevMonth() { currentDate.setMonth(currentDate.getMonth() - 1); renderCalendar(); }
    function nextMonth() { currentDate.setMonth(currentDate.getMonth() + 1); renderCalendar(); }
    renderCalendar();
</script>