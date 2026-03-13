<link rel="stylesheet" href="/Test/Public/Css/schedule.css">
<div class="Page-Schedule">

    <div class="head">
        <button onclick="prevMonth()">❮</button>
        <h2 id="monthYear"></h2>
        <button onclick="nextMonth()">❯</button>
    </div>

    <div class="week">
        <div>CN</div>
        <div>T2</div>
        <div>T3</div>
        <div>T4</div>
        <div>T5</div>
        <div>T6</div>
        <div>T7</div>
    </div>

    <div class="day" id="calendarDays"></div>

</div>
<script>
        let currentDate = new Date();

        function renderCalendar(){

            const daysContainer = document.getElementById("calendarDays");
            const monthYear = document.getElementById("monthYear");

            let year = currentDate.getFullYear();
            let month = currentDate.getMonth();

            let firstDay = new Date(year, month, 1).getDay();
            let lastDate = new Date(year, month + 1, 0).getDate();

            monthYear.innerText = `Tháng ${month+1} - ${year}`;

            daysContainer.innerHTML = "";

            for(let i=0;i<firstDay;i++){
                daysContainer.innerHTML += "<div></div>";
            }

            for(let i=1;i<=lastDate;i++){

                let today = new Date();

                if(
                    i === today.getDate() &&
                    month === today.getMonth() &&
                    year === today.getFullYear()
                ){
                    daysContainer.innerHTML += `<div class="today">${i}</div>`;
                }
                else{
                    daysContainer.innerHTML += `<div>${i}</div>`;
                }

            }

        }

        function prevMonth(){
            currentDate.setMonth(currentDate.getMonth()-1);
            renderCalendar();
        }

        function nextMonth(){
            currentDate.setMonth(currentDate.getMonth()+1);
            renderCalendar();
        }

        renderCalendar();
</script>