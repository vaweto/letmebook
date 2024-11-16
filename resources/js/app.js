import './bootstrap';
import picker from "./picker.js";
import  { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm.js'


Alpine.plugin(picker)

Livewire.start()
