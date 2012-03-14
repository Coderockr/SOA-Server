function RpcClient(apiKey) {
        this.apiKey = apiKey;
        
        this.execute = function (procedure, params) {
            var result;
            var status; 
            
            $.ajax({
                url: '/rpc/' + procedure,
                data: params,
                contentType: 'application/json',
                type: 'POST',
                async: false, 
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