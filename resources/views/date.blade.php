<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Bootstrap CSS -->
        {{--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">--}}
        {{--<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.5.2/darkly/bootstrap.min.css" integrity="sha384-nNK9n28pDUDDgIiIqZ/MiyO3F4/9vsMtReZK39klb/MtkZI3/LtjSjlmyVPS3KdN" crossorigin="anonymous">--}}
        <link rel="stylesheet" href="https://bootswatch.com/4/darkly/bootstrap.min.css">
        <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>

        <!-- JQuery CSS -->
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css" />


        <title>{{ config('app.name') }}</title>
    </head>
    <body>

        <div class="container-fluid" >
            <div class="row justify-content-center">

                <div class="jumbotron align-self-center" style="margin: 0;position: absolute;top: 50%;left: 50%;-ms-transform: translate(-50%, -50%);transform: translate(-50%, -50%);">
                    <h1 class="display-4">{{ config('app.name') }}</h1>
                    <hr class="my-4">
                    <p class="lead">
                        Availability of holidays by date
                    </p>
                    <span id="loader"></span>
                    <div class="input-group input-group-lg">
                        <div id="calendarIconArea" class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="far fa-calendar-alt"></i>
                            </span>
                        </div>
                        <input id="checkDateInput" type="text" class="form-control datepicker" placeholder="Specify the date" aria-label="Specify the date">
                        <div class="input-group-append">
                            <button id="checkDateButton" class="btn btn-primary" style="min-width: 130px;" type="button">Check</button>
                        </div>
                    </div>
                    <div id="resultBox" class="card mt-3 d-none">
                        <div class="card-body">
                            <h4 id="resultTitle" class="card-title"></h4>
                            <p id="resultBody" class="card-text"></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>


        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js"></script>
        <script>
          $('.datepicker').datepicker({
            dateFormat: 'dd.mm.yy',
          });

          $('#calendarIconArea').click(function () {
            $('#checkDateInput').trigger('focus');
          });

          $('#checkDateButton').click(async function () {
            let checkDateInputValue = $('#checkDateInput').val();
            if ( ! checkDateInputValue) {
              $('#checkDateInput').attr('placeholder', 'First choose a date');
              $('#checkDateInput').trigger('focus');
            }

            if (checkDateInputValue) {
              document.getElementById('checkDateButton').disabled = true;
              $("#checkDateButton").html('Checking...');
              let response = await sendData({ 'date': checkDateInputValue });

              if (response.holidays) {
                let holidays = response.holidays;

                $("#resultTitle").text(`Results for ${checkDateInputValue}:`);

                if (typeof holidays !== 'undefined' && holidays.length > 0) {
                  let resultsListHtml = holidays.map(function (holiday) {
                    return '<li>' + holiday + '</li>';
                  }).join('');

                  $("#resultBody").html(resultsListHtml);
                  $('#resultBox').removeClass('border-info').removeClass('border-warning');
                  $('#resultBox').addClass('border-success');
                } else {
                  $("#resultBody").text('Nothing found');
                  $('#resultBox').removeClass('border-success').removeClass('border-warning');
                  $('#resultBox').addClass('border-info');
                }

                $('#resultBox').removeClass('d-none').addClass('d-block');

                $('#checkDateButton').removeClass('btn-primary').addClass('btn-success');
                $('#checkDateButton').html('Done');
              } else {
                $("#resultTitle").text('Warning:');
                $("#resultBody").text(response.error);

                $('#resultBox').removeClass('border-success').removeClass('border-info');
                $('#resultBox').addClass('border-warning');
                $('#resultBox').removeClass('d-none').addClass('d-block');

                $('#checkDateButton').removeClass('btn-primary').addClass('btn-danger');
                $('#checkDateButton').html('Error');
              }

              await sleep(2000);

              $('#checkDateButton').removeClass('btn-success');
              $('#checkDateButton').removeClass('btn-danger');
              $('#checkDateButton').addClass('btn-primary');
              $("#checkDateButton").html('Check');
              document.getElementById('checkDateButton').disabled = false;
            }
          });

          function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
          }

          function formatParams(params) {
            return params.map(function (param) {
              return param.param + ':' + param.childParam;
            }).join(', ');
          }

          async function sendData(data) {
            let array = [];
            await $.ajax({
              url: '{{route('holidays')}}',
              type: 'post',
              data: data
            }).done(function(response){
              array.push(response)
            });
            let object = array[0];
            return object;
          }

        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
    </body>
</html>