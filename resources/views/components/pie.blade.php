<footer class="bg-verde-800 text-neutral-300">
    <div class="mx-auto grid max-w-7xl grid-cols-1 gap-10 px-4 py-12 md:grid-cols-2 lg:grid-cols-4">
        <div>
            <p class="font-display text-2xl font-black uppercase tracking-[0.18em] text-papel">El Campus</p>
            <p class="mt-1 font-sans text-[11px] uppercase tracking-[0.25em] text-oro-500">Periódico Universitario</p>
            <p class="mt-4 text-sm leading-relaxed text-neutral-400">
                Medio digital de la Universidad de Córdoba, Colombia, dedicado a difundir la vida
                universitaria: investigación, cultura, deportes, eventos y comunidad.
            </p>
        </div>

        <div>
            <h3 class="font-sans text-xs font-bold uppercase tracking-[0.18em] text-papel">Secciones</h3>
            <ul class="mt-4 space-y-2 text-sm">
                <li><a href="{{ route('seccion.universidad') }}" class="transition-colors hover:text-papel">Universidad</a></li>
                <li><a href="{{ route('seccion.investigacion') }}" class="transition-colors hover:text-papel">Investigación</a></li>
                <li><a href="{{ route('seccion.cultura') }}" class="transition-colors hover:text-papel">Cultura</a></li>
                <li><a href="{{ route('seccion.deportes') }}" class="transition-colors hover:text-papel">Deportes</a></li>
                <li><a href="{{ route('eventos.publica') }}" class="transition-colors hover:text-papel">Eventos</a></li>
                <li><a href="{{ route('seccion.opinion') }}" class="transition-colors hover:text-papel">Opinión</a></li>
            </ul>
        </div>

        <div>
            <h3 class="font-sans text-xs font-bold uppercase tracking-[0.18em] text-papel">Universidad</h3>
            <ul class="mt-4 space-y-2 text-sm">
                <li><a href="https://unicordoba.edu.co" target="_blank" rel="noopener" class="transition-colors hover:text-papel">Sitio institucional</a></li>
                <li><a href="{{ route('sobre-el-campus') }}" class="transition-colors hover:text-papel">Sobre El Campus</a></li>
                <li><a href="#" class="transition-colors hover:text-papel">Contacto</a></li>
                <li><a href="#" class="transition-colors hover:text-papel">Política editorial</a></li>
            </ul>
        </div>

        <div>
            <h3 class="font-sans text-xs font-bold uppercase tracking-[0.18em] text-papel">Contacto</h3>
            <p class="mt-4 text-sm leading-relaxed text-neutral-400">
                Redacción El Campus<br>
                Campus Universitario de Berástegui, Montería, Córdoba, Colombia
            </p>
            <p class="mt-3 text-sm text-neutral-300">contacto@elcampus.edu.co</p>
            <div class="mt-4 flex gap-4 font-sans text-xs uppercase tracking-wider text-neutral-400">
                <a href="#" class="transition-colors hover:text-papel">Facebook</a>
                <a href="#" class="transition-colors hover:text-papel">Instagram</a>
                <a href="#" class="transition-colors hover:text-papel">YouTube</a>
            </div>
        </div>
    </div>

    <div class="border-t border-verde-900">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-5 font-sans text-xs text-neutral-500 md:flex-row">
            <span>© {{ date('Y') }} El Campus. Todos los derechos reservados.</span>
            <span>Universidad de Córdoba · Montería, Colombia</span>
        </div>
    </div>
</footer>