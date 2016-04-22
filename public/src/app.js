var APIEndpoint = React.createClass({
    render: function() {
        return (
            <div id="container-apiendpoint">
                <label for="apiendpoint">API Endpoint: </label><input type="textbox" name="apiendpoint" />
            </div>
        )
    }
});

var APIToken = React.createClass({
    render: function() {
        return (
            <div id="container-apitoken">
                <label for="apitoken">API Token: </label><input type="textbox" name="apitoken" />
            </div>
        )
    }
});

var onChange = function() {
    ReactDOM.render(
        <div class="app">
            <APIEndpoint />
            <APIToken />
        </div>,
        document.getElementById('example')
    );
}

onChange();