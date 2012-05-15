function RpcClient (apiKey, callbackList) {
        this.apiKey = apiKey;
        this.callbackList = {
            "success": function (data) { console.log('success', data); },
            "error": function (data) { console.log('error', data); },
            "beforeSend": function (jqXHR, settings) {
                $('#loading-overlay').fadeIn();
            },
            "complete": function (jqXHR, textStatus) {
                $('#loading-overlay').fadeOut();
            }
        };

        for (var callback in callbackList) {
            this.callbackList[callback] = callbackList[callback];
        }

        this.execute = function (procedure, params) {
            var result;
            var status; 

            $.ajax({
                url: procedure,
                beforeSend: this.callbackList.beforeSend,
                data: params,
                dataType: 'json',
                type: 'POST',
                crossDomain: true,
                cache: false,
                headers: {
                    "Authorization": this.apiKey
                },
                complete: this.callbackList.complete,
                statusCode: {
                    200: this.callbackList.success,
                    500: this.callbackList.error
                }
            }); 
        }
    }