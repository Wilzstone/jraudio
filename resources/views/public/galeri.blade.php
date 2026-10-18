@include('public.header')

<section class="page-header page-header-gradient">
    <span class="eyebrow">Dokumentasi</span>
    <h1>Galeri Acara</h1>
    <p>Beberapa dokumentasi dan contoh suasana acara yang dapat ditangani oleh JR Audio.</p>
</section>

<section class="section">
    <div class="section-title">
        <span class="eyebrow">Portfolio Event</span>
        <h2>Dokumentasi JR Audio</h2>
        <p>Galeri ini dapat diganti dengan foto asli saat pemasangan sound system, acara wedding, seminar, live music, atau event outdoor.</p>
    </div>

    <div class="gallery-grid">
        <div class="gallery-item">
            <img src="/assets/img/galeri/event1.jpeg" alt="Dokumentasi acara JR Audio">
            <div>
                <h3>Setup Event</h3>
                <p>Sound system untuk acara formal.</p>
            </div>
        </div>
        <div class="gallery-item">
            <img src="/assets/img/galeri/event2.jpeg" alt="Setup panggung JR Audio">
            <div>
                <h3>Event Indoor</h3>
                <p>Penataan perangkat audio.</p>
            </div>
        </div>
        <div class="gallery-item">
            <img src="/assets/img/galeri/event3.jpeg" alt="Live performance JR Audio">
            <div>
                <h3>Event Outdoor</h3>
                <p>Kebutuhan audio untuk musik dan hiburan.</p>
            </div>
        </div>
        <div class="gallery-item">
            <img src="/assets/img/galeri/event4.jpg" alt="Event takbiran keliling JR Audio">
            <div>
                <h3>Event Takbiran</h3>
                <p>Sound system untuk acara takbiran keliling.</p>
            </div>
        </div>
    </div>
</section>


<section class="section section-soft">
    <div class="section-title">
        <span class="eyebrow">List Event</span>
        <h2>Jenis Event yang Pernah Didukung</h2>
        <p>Beberapa contoh acara yang dapat didukung oleh JR Audio.</p>
    </div>
    <div class="event-list-grid">
        <span>Seminar</span><span>Acara Indoor</span><span>Makrab</span><span>Live Music</span>
        <span>Wedding</span><span>17-an</span><span>Dangdutan</span><span>Jathilan</span>
        <span>Wayang</span><span>Misa Gereja</span><span>Takbiran</span><span>Open Ceremony</span>
        <span>Wisuda / Kelulusan</span>
    </div>
</section>


<section class="section">
    <div class="section-title">
        <span class="eyebrow">Video</span>
        <h2>Video Dokumentasi</h2>
    </div>

    <div class="video-grid">
        <div class="video-card">
            <video controls>
                <source src="/assets/video/vid1.mp4" type="video/mp4">
                Browser tidak mendukung video.
            </video>
            <h3>Video Event 1</h3>
        </div>
        <div class="video-card">
            <video controls>
                <source src="/assets/video/vid2.mp4" type="video/mp4">
                Browser tidak mendukung video.
            </video>
            <h3>Video Event 2</h3>
        </div>
    </div>
</section>

@include('public.footer')