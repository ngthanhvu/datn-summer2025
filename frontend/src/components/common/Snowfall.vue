<template>
  <div class="snowfall" :style="style">
    <div
      v-for="n in snowCount"
      :key="n"
      class="snowflake"
      :style="getSnowStyle(n)"
    >❄️</div>
  </div>
</template>

<script setup>
const snowCount = 40
const style = {
  position: 'absolute',
  top: 0,
  left: 0,
  width: '100%',
  height: '100%',
  pointerEvents: 'none',
  zIndex: 10,
}
function getSnowStyle(n) {
  const left = Math.random() * 100
  const duration = 7 + Math.random() * 7 
  const delay = Math.random() * 5
  const size = 12 + Math.random() * 16
  return {
    left: `${left}%`,
    animationDuration: `${duration}s`,
    animationDelay: `${delay}s`,
    fontSize: `${size}px`,
  }
}
</script>

<style scoped>
.snowfall {
  pointer-events: none;
  overflow: hidden;
}
.snowflake {
  position: absolute;
  top: -10%;
  color: #b3e0ff;
  opacity: 0.8;
  animation-name: snow-fall;
  animation-timing-function: linear;
  animation-iteration-count: infinite;
}
@keyframes snow-fall {
  0% { transform: translateY(0); opacity: 0.8; }
  80% { opacity: 0.8; }
  100% { transform: translateY(110vh); opacity: 0; }
}
</style> 