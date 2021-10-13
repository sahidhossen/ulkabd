import request from 'superagent';
import React from "react";
import Echo from "laravel-echo";
import Config from "../components/config";
import i18n from "../plugins/i18n.js"
let config = new Config();

class TrainAgent extends React.Component {

    constructor( props ){
        super( props );

        this.state = {
            training_status: 2,
            count:1
        }
        this.onAction = this.onAction.bind(this);
    }

    componentDidMount() {
       this.updateAgentTrainBtn();
       this.listenBroadCast();
    }

    componentWillUnmount(){
        console.debug("This is will unmount functions ");
    }

    listenBroadCast(){
        if (typeof io !== 'undefined') {
            window.Echo = new Echo({
                broadcaster: 'socket.io',
                host: config.root + ':6001'
            });
            window.Echo.private('channel-agent-train_' + config.userID)
                .listen('BroadcastTrainingStatus', (response) => {
                    //console.debug("Broadcast Status: ", response);
                    this.setState({training_status: response.trainingStatus.status});
                });
        }
    }

    /*
     * status = {'needed' => 0,'running' => 1,'done' => 2 }
     */
    updateAgentTrainBtn(){
        this.setState({ training_status: 1 });
        let CheckTrainingStatus = request
            .post('/api/check_training_status')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest');
        CheckTrainingStatus.end((err, response) => {
            if (err) {
                console.error(err);
            }
            if(response.error==false){
                this.setState({ training_status: response.body.status })
            }
        });

    }
    onAction(event){
        event.preventDefault();
        this.setState({ training_status: 1 });
        let fireAction = request
            .post('/api/train_agent')
             //.post('/api/test_broadcast')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest');
        fireAction.end((err, response) => {
            if (err) {
                console.error(err);
            }
            console.debug("Clicked ajax response: ", response.body );
            if(response.error==true){
                this.setState({ training_status: 0 });
            }
        });
    }

    render(){
        let trainingBtn = "btn btn-admin btn-admin-red";
        if(this.state.training_status == 1 ){
            trainingBtn = "btn btn-admin btn-admin-red disabled btn-running";
        }
        if(this.state.training_status == 2 ){
            trainingBtn = "btn btn-admin btn-admin-success disabled";
        }
        // console.debug("current status: ", this.state.training_status );

        return (
            <div>
                <p>
                    <a href="#" id="trainAgent" onClick={this.onAction}  className={trainingBtn} > <span><i className="fa fa-gear"></i></span> {i18n.t('common.buttons.trainAgent')} </a>
                </p>
            </div>
        )
    }
}

export default TrainAgent;