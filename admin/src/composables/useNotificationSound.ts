import notificationSound from "../assets/notification.mp3";

export function useNotificationSound() {
  const audio = new Audio(notificationSound);
  audio.volume = 0.5;
  audio.preload = "auto";

  function play() {
    // Reinicia por si llegan varias notificaciones seguidas y el audio
    // anterior no terminó de sonar
    audio.currentTime = 0;
    audio.play().catch(() => {
      // El navegador bloquea el autoplay hasta que el usuario haya
      // interactuado con la página al menos una vez — se ignora en silencio
    });
  }

  return { play };
}
