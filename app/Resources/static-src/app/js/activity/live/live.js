import ActivityEmitter from '../activity-emitter';
export default class LiveShow {
  constructor() {
    this.interval = 1;
    this.emitter = new ActivityEmitter();
    this.startEvent();
    this.finishByReplay();
    this.countdownEvent();
  }

  finishByReplay() {
    let self = this;
    $('.js-replay').on('click', function(){
      let triggerUrl = $(this).data('finish');
      $.post(triggerUrl, function(res){
        self.emitter.emit('finish');
      });
    });
  }

  startEvent() {
    let self = this;
    $('.js-start-live').on('click', function () {
      let triggerUrl = $(this).data('finish');
      $.post(triggerUrl, function(res){
        if (res.status === 'not_start') {
          return ;
        }
        if (!self.started) {
          this.started = true;
          self.emitter.emit('start', {}).then(() => {
            console.log('live.start');
          }).catch((error) => {
            console.error(error);
          });
        }
      });
    });
  }

  countdownEvent() {
    let $countdown = $('#countdown');
    if ($countdown.length == 0) return;

    this.timeRemain = $countdown.data('timeRemain');
    this._countdown($countdown, this.interval);

    this.iId = setInterval(()=> {
      this._countdown($countdown, this.interval);
    }, this.interval * 1000);
  }
  _countdown($countdown, interval){
    let timeRemain = this.timeRemain;
    let days = Math.floor(timeRemain / (60 * 60 * 24));
    let modulo = timeRemain % (60 * 60 * 24);
    let hours = Math.floor(modulo / (60 * 60));
    modulo = modulo % (60 * 60);
    let minutes = Math.floor(modulo / 60);
    let seconds = modulo % 60;
    let context = '';
    context += days ? days + Translator.trans('activity.live.day') : '';
    context += hours ? hours + Translator.trans('activity.live.hour') : '';
    context += minutes ? minutes + Translator.trans('activity.live.minute') : '';
    context += seconds ? seconds + Translator.trans('activity.live.second') : '';
    this.timeRemain = timeRemain - interval;
    $countdown.text(context);
    if (this.timeRemain<=0) {
      $countdown.text(Translator.trans('activity.live.started_tip'));
      window.clearInterval(this.iId);
    }
  }
}