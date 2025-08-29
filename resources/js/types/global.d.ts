
import * as Ably from 'ably';

interface Window {
  CURRENT_USER_ID?: string;
  currentAudio?: HTMLAudioElement;
  Ably?: typeof Ably;
}
