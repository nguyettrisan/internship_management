var jq = jQuery;

jq(document).ready(function () {

    jq('#internship-calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        locale: 'vi',
        firstDay: 1,
        height: "auto",

        events: function(start, end, timezone, callback){
            jq.ajax({
                url: admin_url + 'internship_management/internship_calendar/events',
                data: {
                    start: start.format('YYYY-MM-DD'),
                    end: end.format('YYYY-MM-DD')
                },
                success: function(res){
                    callback(res);
                }
            });
        }
    });

});