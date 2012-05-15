function RestClient(apiKey, callbackList) {
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
    
    this.get = function (url) {
        return this.send(url, 'GET');
    }

    this.post = function (url, data) {
        return this.send(url, 'POST', data);
    }

    this.put = function (url, data) {
        return this.send(url, 'PUT', data);
    }

    this.delete = function (url) {
        $.ajax({
            url: url,
            type: 'DELETE',
            crossDomain: true,
            beforeSend: this.callbackList.beforeSend,
            headers: {
                "Authorization": this.apiKey
            },
            complete: this.callbackList.complete,
            statusCode: {
                200: this.callbackList.success,
                404: this.callbackList.error
            }
        });
    }

    this.send = function (url, method, data) {
        $.ajax({
            url: url,
            type: method,
            crossDomain: true,
            beforeSend: this.callbackList.beforeSend,
            data: data,
            dataType: "json",
            cache: false,
            headers: {
                "Authorization": this.apiKey
            },
            complete: this.callbackList.complete,
            success: this.callbackList.success,
            error: this.callbackList.error
        });
    }
}