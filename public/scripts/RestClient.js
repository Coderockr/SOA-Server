function RestClient(apiKey) {
        this.apiKey = apiKey;
        
        this.get = function (url) {
            return this.send(url,'GET');
        }

        this.post = function (url, data) {
            return this.send(url,'POST', data);
        }

        this.put = function (url, data) {
            return this.send(url,'PUT', data);
        }

        this.delete = function (url) {
            var status;
            $.ajax({
                url: url,
                type: 'DELETE',
                async: false, 
                headers: {
                    "Authorization": this.apiKey
                },
                statusCode: {
                    200: function() {
                        status = 'success';
                    },
                    404: function(data) {
                        status = 'error';
                    }
                }
            });
            return {status: status};
        }

        this.send = function (url, method, data){
            var result;
            var status;
            $.ajax({
                url: url,
                data: data,
                type: method,
                async: false, 
                cache: false,
                headers: {
                    "Authorization": this.apiKey
                },
                success: function(data){
                    result = data;
                    status = 'success';

                },
                error: function(data) {
                    result = data.responseText;
                    status = 'error';
                }
            });
            return {status: status, data: result};
        }
    }