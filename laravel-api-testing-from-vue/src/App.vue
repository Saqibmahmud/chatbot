<script setup>
import { onMounted ,ref} from 'vue';
import HelloWorld from './components/HelloWorld.vue'

const prompts=ref([])


onMounted(()=>{
  fetch('http://127.0.0.1:8000/api/v1/image-generations')
  .then(response=>response.json())
  .then(result=>{
    debugger
    prompts.value=result.data
  })

})
</script>

<template>
  <div>
    <a href="https://vite.dev" target="_blank">
      <img src="/vite.svg" class="logo" alt="Vite logo" />
    </a>
    <a href="https://vuejs.org/" target="_blank">
      <img src="./assets/vue.svg" class="logo vue" alt="Vue logo" />
    </a>
  </div>
  <HelloWorld msg="Vite + Vue" />
</template>

<style scoped>
.logo {
  height: 6em;
  padding: 1.5em;
  will-change: filter;
  transition: filter 300ms;
}
.logo:hover {
  filter: drop-shadow(0 0 2em #646cffaa);
}
.logo.vue:hover {
  filter: drop-shadow(0 0 2em #42b883aa);
}
</style>
