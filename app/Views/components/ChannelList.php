<aside class="w-60 h-screen bg-[#2B2D31] flex flex-col shrink-0 z-10">
  <header class="h-12 flex items-center justify-between px-4 font-semibold text-gray-100 hover:bg-[#35373C] cursor-pointer transition-colors shadow-[0_1px_2px_rgba(0,0,0,0.2)] z-20">
    <h2 class="truncate">Comunidade Farn-Chat</h2>
    <span class="text-sm">⌄</span>
  </header>

  <div class="flex-1 overflow-y-auto scrollbar-hide pt-4 pb-2 px-2 space-y-4">
    
    <div>
      <div class="flex items-center text-xs font-semibold text-gray-400 hover:text-gray-200 cursor-pointer px-1 mb-1 tracking-wide">
        <span class="mr-1 text-[10px]">▼</span>
        CANAIS DE TEXTO
      </div>
      
      <div class="space-y-[2px]">
        <div class="flex items-center px-2 py-1.5 bg-[#404249] rounded-md cursor-pointer text-white group">
          <span class="text-gray-400 mr-1.5 text-lg font-light group-hover:text-gray-300">#</span>
          <span class="truncate font-medium">geral</span>
        </div>

        <div class="flex items-center px-2 py-1.5 hover:bg-[#35373C] rounded-md cursor-pointer text-gray-200 group transition-colors">
          <span class="text-gray-400 mr-1.5 text-lg font-light">#</span>
          <span class="truncate font-semibold">dev-updates</span>
          <div class="ml-auto w-1.5 h-1.5 bg-white rounded-full"></div>
        </div>
      </div>
    </div>

  </div>

  <footer class="h-[52px] bg-[#232428] flex items-center px-2 justify-between shrink-0">
    <div class="flex items-center gap-2 hover:bg-[#3F4147] p-1.5 rounded-md cursor-pointer transition-colors max-w-[120px]">
      <div class="w-8 h-8 rounded-full bg-blue-500 shrink-0 flex items-center justify-center text-white text-xs font-bold">
        FC
      </div>
      <div class="flex flex-col overflow-hidden">
        <span class="text-sm font-semibold text-white truncate leading-tight">Admin</span>
        <span class="text-xs text-gray-400 truncate leading-tight">Online</span>
      </div>
    </div>

    <div class="flex items-center text-gray-400">
      <button class="w-8 h-8 flex items-center justify-center hover:bg-[#3F4147] hover:text-gray-200 rounded-md transition-colors">🎤</button>
      <button class="w-8 h-8 flex items-center justify-center hover:bg-[#3F4147] hover:text-gray-200 rounded-md transition-colors">🎧</button>
      <button class="w-8 h-8 flex items-center justify-center hover:bg-[#3F4147] hover:text-gray-200 rounded-md transition-colors">⚙️</button>
    </div>
  </footer>
</aside>
