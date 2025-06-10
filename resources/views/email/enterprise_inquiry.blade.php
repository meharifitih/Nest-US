@component('mail::message')
# New Enterprise Package Inquiry

**Name:** {{ $data['name'] ?? '' }}  
**Email:** {{ $data['email'] ?? '' }}  
**Number of Units:** {{ $data['units'] ?? '' }}  
**Number of Properties:** {{ $data['properties'] ?? '' }}  
**Interval:** {{ $data['interval'] ?? '' }}  
@if(!empty($data['message']))
**Message:**
{{ $data['message'] }}
@endif

---

_This inquiry was submitted via the Enterprise Contact Us form._
@endcomponent 